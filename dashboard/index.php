<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Start Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


$userId = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$user = $_SESSION['user'] ?? [];

$fullname =
    $user['fullname'] ??
    'User';


/*
|--------------------------------------------------------------------------
| Get Active Wallets
|--------------------------------------------------------------------------
|
| Wallet balance calculation:
|
| Initial Balance
| + Income
| - Expense
| - Transfer Sent
| + Transfer Received
|
|--------------------------------------------------------------------------
*/

$walletSql = "
    SELECT

        w.id,

        w.name,

        w.description,

        w.initial_balance,

        w.currency,

        w.status,

        wt.name AS wallet_type,

        (
            w.initial_balance

            /* -------------------------------------------------
               INCOME
               ------------------------------------------------- */

            + COALESCE(
                (
                    SELECT SUM(t.amount)
                    FROM transactions t
                    WHERE t.wallet_id = w.id
                      AND t.user_id = :income_user_id
                      AND t.type = 'Income'
                ),
                0
            )

            /* -------------------------------------------------
               EXPENSE
               ------------------------------------------------- */

            - COALESCE(
                (
                    SELECT SUM(t.amount)
                    FROM transactions t
                    WHERE t.wallet_id = w.id
                      AND t.user_id = :expense_user_id
                      AND t.type = 'Expense'
                ),
                0
            )

            /* -------------------------------------------------
               ADJUSTMENT
               
               Increase = positive amount
               Decrease = negative amount
               ------------------------------------------------- */

            + COALESCE(
                (
                    SELECT SUM(t.amount)
                    FROM transactions t
                    WHERE t.wallet_id = w.id
                      AND t.user_id = :adjustment_user_id
                      AND t.type = 'Adjustment'
                ),
                0
            )

            /* -------------------------------------------------
               TRANSFER SENT
               ------------------------------------------------- */

            - COALESCE(
                (
                    SELECT SUM(source_t.amount)

                    FROM transaction_transfers tt

                    INNER JOIN transactions source_t
                        ON tt.transaction_id = source_t.id

                    WHERE source_t.wallet_id = w.id
                      AND source_t.user_id = :sent_user_id
                ),
                0
            )

            /* -------------------------------------------------
               TRANSFER RECEIVED
               ------------------------------------------------- */

            + COALESCE(
                (
                    SELECT SUM(destination_t.amount)

                    FROM transaction_transfers tt

                    INNER JOIN transactions destination_t
                        ON tt.destination_transaction_id =
                           destination_t.id

                    WHERE destination_t.wallet_id = w.id
                      AND destination_t.user_id = :received_user_id
                ),
                0
            )

        ) AS current_balance

    FROM wallets w

    INNER JOIN wallet_types wt
        ON w.wallet_type_id = wt.id

    WHERE w.user_id = :wallet_user_id

      AND w.status = 'Active'

    ORDER BY w.name ASC
";


$walletStmt =
    $pdo->prepare($walletSql);


$walletStmt->execute([

    ':income_user_id' =>
        $userId,

    ':expense_user_id' =>
        $userId,

    ':adjustment_user_id' =>
        $userId,

    ':sent_user_id' =>
        $userId,

    ':received_user_id' =>
        $userId,

    ':wallet_user_id' =>
        $userId

]);


$wallets =
    $walletStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Calculate Total Balance
|--------------------------------------------------------------------------
*/

$totalBalance = 0;

foreach ($wallets as $wallet) {

    $totalBalance +=
        (float) $wallet['current_balance'];

}


$activeWalletCount =
    count($wallets);


/*
|--------------------------------------------------------------------------
| Total Income
|--------------------------------------------------------------------------
*/

$incomeSql = "
    SELECT
        COALESCE(
            SUM(amount),
            0
        ) AS total_income

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Income'
";


$incomeStmt =
    $pdo->prepare($incomeSql);


$incomeStmt->execute([
    ':user_id' => $userId
]);


$totalIncome =
    (float) $incomeStmt
        ->fetchColumn();


/*
|--------------------------------------------------------------------------
| Total Expenses
|--------------------------------------------------------------------------
*/

$expenseSql = "
    SELECT
        COALESCE(
            SUM(amount),
            0
        ) AS total_expense

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Expense'
";


$expenseStmt =
    $pdo->prepare($expenseSql);


$expenseStmt->execute([
    ':user_id' => $userId
]);


$totalExpense =
    (float) $expenseStmt
        ->fetchColumn();


/*
|--------------------------------------------------------------------------
| Recent Transactions
|--------------------------------------------------------------------------
|
| We display the latest 8 transactions.
|
|--------------------------------------------------------------------------
*/

$recentSql = "
    SELECT

        t.id,

        t.type,

        t.amount,

        t.transaction_date,

        t.description,

        t.wallet_id,

        w.name AS wallet_name,

        c.name AS category_name,

        destination_t.id AS destination_transaction_id,

        destination_w.name AS destination_wallet_name

    FROM transactions t

    INNER JOIN wallets w
        ON t.wallet_id = w.id

    LEFT JOIN categories c
        ON t.category_id = c.id

    LEFT JOIN transaction_transfers tt
        ON tt.transaction_id = t.id

    LEFT JOIN transactions destination_t
        ON tt.destination_transaction_id =
           destination_t.id

    LEFT JOIN wallets destination_w
        ON destination_t.wallet_id =
           destination_w.id

    WHERE t.user_id = :user_id

      /*
       * Do not show the destination side
       * of a transfer as a second transaction.
       *
       * The source transaction already contains
       * the complete transfer information.
       */
      AND NOT EXISTS (

          SELECT 1

          FROM transaction_transfers destination_link

          WHERE destination_link.destination_transaction_id =
                t.id

      )

    ORDER BY

        t.transaction_date DESC,

        t.id DESC

    LIMIT 8
";


$recentStmt =
    $pdo->prepare($recentSql);


$recentStmt->execute([
    ':user_id' => $userId
]);


$recentTransactions =
    $recentStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function dashboardMoney(
    float $amount,
    string $currency = 'PHP'
): string {

    $symbol =
        $currency === 'PHP'
            ? '₱'
            : $currency . ' ';

    return $symbol .
        number_format(
            $amount,
            2
        );

}


function dashboardTypeClass(
    string $type
): string {

    switch ($type) {

        case 'Income':
            return 'income';

        case 'Expense':
            return 'expense';

        case 'Transfer':
            return 'transfer';

        case 'Adjustment':
            return 'adjustment';

        default:
            return '';

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Dashboard - <?= e(APP_NAME) ?>
</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- Bootstrap Icons -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>


<link
    rel="stylesheet"
    href="<?= BASE_URL ?>assets/css/style_dashboard_index.css"
>

</head>

<body>

<!-- =========================================================
     MOBILE HEADER
========================================================= -->

<nav
    class="
        navbar
        navbar-dark
        bg-success
        mobile-header
        d-md-none
    "
>

<div
    class="
        container-fluid
        px-3
    "
>

    <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#mobileMenu"
    >

        <span
            class="navbar-toggler-icon"
        ></span>

    </button>


    <span
        class="
            navbar-brand
            mb-0
            fw-bold
        "
    >

        💰 <?= e(APP_NAME) ?>

    </span>


    <a
        href="<?= BASE_URL ?>auth/logout.php"
        class="text-white"
    >

        <i
            class="
                bi
                bi-box-arrow-right
            "
        ></i>

    </a>

</div>

</nav>

<!-- =========================================================
     MOBILE OFFCANVAS
========================================================= -->

<div
    class="
        offcanvas
        offcanvas-start
    "
    tabindex="-1"
    id="mobileMenu"
>

<div
    class="
        offcanvas-header
        bg-success
        text-white
    "
>

    <h5
        class="
            offcanvas-title
            fw-bold
        "
    >

        💰 <?= e(APP_NAME) ?>

    </h5>


    <button
        type="button"
        class="btn-close btn-close-white"
        data-bs-dismiss="offcanvas"
    ></button>

</div>


<div
    class="
        offcanvas-body
        p-0
    "
>

    <div
        class="
            list-group
            list-group-flush
        "
    >

        <a
            href="<?= BASE_URL ?>dashboard/"
            class="
                list-group-item
                list-group-item-action
                active
            "
        >

            <i
                class="
                    bi
                    bi-speedometer2
                    me-2
                "
            ></i>

            Dashboard

        </a>


        <a
            href="<?= BASE_URL ?>wallets/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i
                class="
                    bi
                    bi-wallet2
                    me-2
                "
            ></i>

            My Wallets

        </a>


        <a
            href="<?= BASE_URL ?>transactions/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i
                class="
                    bi
                    bi-arrow-left-right
                    me-2
                "
            ></i>

            Transactions

        </a>

        <a
            href="<?= BASE_URL ?>categories/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i
                class="
                    bi
                    bi-tags
                    me-2
                "
            ></i>

            Categories

        </a>

        <a
            href="<?= BASE_URL ?>reports/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i
                class="
                    bi
                    bi-bar-chart
                    me-2
                "
            ></i>

            Reports

        </a>


        <a
            href="<?= BASE_URL ?>profile/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i
                class="
                    bi
                    bi-person
                    me-2
                "
            ></i>

            Profile

        </a>


        <?php if (
            ($user['role'] ?? '') === 'Admin'
        ): ?>

            <a
                href="<?= BASE_URL ?>users/"
                class="
                    list-group-item
                    list-group-item-action
                "
            >

                <i
                    class="
                        bi
                        bi-people
                        me-2
                    "
                ></i>

                User Management

            </a>

        <?php endif; ?>


        <a
            href="<?= BASE_URL ?>auth/logout.php"
            class="
                list-group-item
                list-group-item-action
                text-danger
            "
        >

            <i
                class="
                    bi
                    bi-box-arrow-right
                    me-2
                "
            ></i>

            Logout

        </a>

    </div>

</div>

</div>

<!-- =========================================================
     DESKTOP SIDEBAR
========================================================= -->

<div
    class="
        d-none
        d-md-flex
        flex-column
        bg-white
        border-end
        position-fixed
        top-0
        bottom-0
    "
    style="
        width: 250px;
    "
>

<div
    class="
        p-3
        bg-success
        text-white
    "
>

    <h5
        class="
            mb-0
            fw-bold
        "
    >

        💰 <?= e(APP_NAME) ?>

    </h5>

</div>


<div
    class="
        list-group
        list-group-flush
        flex-grow-1
    "
>

    <a
        href="<?= BASE_URL ?>dashboard/"
        class="
            list-group-item
            list-group-item-action
            active
        "
    >

        <i
            class="
                bi
                bi-speedometer2
                me-2
            "
        ></i>

        Dashboard

    </a>


    <a
        href="<?= BASE_URL ?>wallets/"
        class="
            list-group-item
            list-group-item-action
        "
    >

        <i
            class="
                bi
                bi-wallet2
                me-2
            "
        ></i>

        My Wallets

    </a>


    <a
        href="<?= BASE_URL ?>transactions/"
        class="
            list-group-item
            list-group-item-action
        "
    >

        <i
            class="
                bi
                bi-arrow-left-right
                me-2
            "
        ></i>

        Transactions

    </a>

    <a
        href="<?= BASE_URL ?>categories/"
        class="
            list-group-item
            list-group-item-action
        "
    >

        <i
            class="
                bi
                bi-tags
                me-2
            "
        ></i>

        Categories

    </a>


    <a
        href="<?= BASE_URL ?>reports/"
        class="
            list-group-item
            list-group-item-action
            
        "
    >

        <i
            class="
                bi
                bi-bar-chart
                me-2
            "
        ></i>

        Reports

    </a>


    <a
        href="<?= BASE_URL ?>profile/"
        class="
            list-group-item
            list-group-item-action
        "
    >

        <i
            class="
                bi
                bi-person
                me-2
            "
        ></i>

        Profile

    </a>


    <?php if (
        ($user['role'] ?? '') === 'Admin'
    ): ?>

        <a
            href="<?= BASE_URL ?>users/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i
                class="
                    bi
                    bi-people
                    me-2
                "
            ></i>

            User Management

        </a>

    <?php endif; ?>

</div>


<div
    class="
        p-3
        border-top
    "
>

    <a
        href="<?= BASE_URL ?>auth/logout.php"
        class="
            btn
            btn-outline-danger
            w-100
        "
    >

        <i
            class="
                bi
                bi-box-arrow-right
                me-1
            "
        ></i>

        Logout

    </a>

</div>

</div>

<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main
    class="
        dashboard-content
    "
>

<!-- =====================================================
     WELCOME
====================================================== -->

<div
    class="
        mb-4
    "
>

    <h3
        class="
            fw-bold
            mb-1
        "
    >

        Good day,
        <?= e($fullname) ?>! 👋

    </h3>


    <p
        class="
            text-muted
            mb-0
        "
    >

        Here's your financial overview.

    </p>

</div>


<!-- =====================================================
     TOTAL BALANCE
====================================================== -->

<div
    class="
        card
        balance-card
        mb-4
    "
>

    <div
        class="
            card-body
            p-4
        "
    >

        <div
            class="
                balance-label
            "
        >

            Total Money

        </div>


        <div
            class="
                balance-value
            "
        >

            <?= dashboardMoney(
                $totalBalance
            ) ?>

        </div>


        <div
            class="
                small
                mt-2
            "
            style="
                opacity: 0.8;
            "
        >

            Across
            <?= $activeWalletCount ?>
            active wallet<?= $activeWalletCount === 1 ? '' : 's' ?>

        </div>

    </div>

</div>


<!-- =====================================================
     SUMMARY CARDS
====================================================== -->

<div
    class="
        row
        g-3
        mb-4
    "
>


    <!-- Income -->

    <div
        class="
            col-6
            col-md-4
        "
    >

        <div
            class="
                card
                summary-card
            "
        >

            <div
                class="
                    card-body
                "
            >

                <div
                    class="
                        summary-icon
                        income-icon
                        mb-3
                    "
                >

                    <i
                        class="
                            bi
                            bi-arrow-down-left
                        "
                    ></i>

                </div>


                <small
                    class="
                        text-muted
                    "
                >

                    Total Income

                </small>


                <h5
                    class="
                        fw-bold
                        mb-0
                        mt-1
                    "
                >

                    <?= dashboardMoney(
                        $totalIncome
                    ) ?>

                </h5>

            </div>

        </div>

    </div>


    <!-- Expense -->

    <div
        class="
            col-6
            col-md-4
        "
    >

        <div
            class="
                card
                summary-card
            "
        >

            <div
                class="
                    card-body
                "
            >

                <div
                    class="
                        summary-icon
                        expense-icon
                        mb-3
                    "
                >

                    <i
                        class="
                            bi
                            bi-arrow-up-right
                        "
                    ></i>

                </div>


                <small
                    class="
                        text-muted
                    "
                >

                    Total Expenses

                </small>


                <h5
                    class="
                        fw-bold
                        mb-0
                        mt-1
                    "
                >

                    <?= dashboardMoney(
                        $totalExpense
                    ) ?>

                </h5>

            </div>

        </div>

    </div>


    <!-- Wallets -->

    <div
        class="
            col-12
            col-md-4
        "
    >

        <div
            class="
                card
                summary-card
            "
        >

            <div
                class="
                    card-body
                "
            >

                <div
                    class="
                        summary-icon
                        wallet-icon
                        mb-3
                    "
                >

                    <i
                        class="
                            bi
                            bi-wallet2
                        "
                    ></i>

                </div>


                <small
                    class="
                        text-muted
                    "
                >

                    Active Wallets

                </small>


                <h5
                    class="
                        fw-bold
                        mb-0
                        mt-1
                    "
                >

                    <?= $activeWalletCount ?>

                </h5>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     QUICK ACTIONS
====================================================== -->

<div
    class="
        row
        g-2
        mb-4
    "
>

    <div
        class="
            col-6
        "
    >

        <a
            href="<?= BASE_URL ?>transactions/create.php"
            class="
                btn
                btn-success
                w-100
                py-2
            "
        >

            <i
                class="
                    bi
                    bi-plus-circle
                    me-1
                "
            ></i>

            Add Transaction

        </a>

    </div>


    <div
        class="
            col-6
        "
    >

        <a
            href="<?= BASE_URL ?>transactions/"
            class="
                btn
                btn-outline-success
                w-100
                py-2
            "
        >

            <i
                class="
                    bi
                    bi-list
                    me-1
                "
            ></i>

            Transactions

        </a>

    </div>

</div>


<!-- =====================================================
     MY WALLETS
====================================================== -->

<div
    class="
        card
        wallet-card
        mb-4
    "
>

    <div
        class="
            card-body
        "
    >

        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-3
            "
        >

            <h5
                class="
                    fw-bold
                    mb-0
                "
            >

                My Wallets

            </h5>


            <a
                href="<?= BASE_URL ?>wallets/"
                class="
                    small
                    text-success
                    text-decoration-none
                "
            >

                View All

            </a>

        </div>


        <?php if (
            empty($wallets)
        ): ?>


            <div
                class="
                    text-center
                    py-4
                "
            >

                <i
                    class="
                        bi
                        bi-wallet2
                        fs-1
                        text-muted
                    "
                ></i>


                <p
                    class="
                        text-muted
                        mt-2
                        mb-3
                    "
                >

                    You don't have any
                    active wallets yet.

                </p>


                <a
                    href="<?= BASE_URL ?>wallets/"
                    class="
                        btn
                        btn-outline-success
                    "
                >

                    Add Wallet

                </a>

            </div>


        <?php else: ?>


            <?php foreach (
                $wallets as $wallet
            ): ?>


                <div
                    class="
                        d-flex
                        align-items-center
                        justify-content-between
                        py-3
                        border-bottom
                    "
                >

                    <div
                        class="
                            d-flex
                            align-items-center
                            gap-3
                        "
                    >

                        <div
                            class="
                                summary-icon
                                wallet-icon
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-wallet2
                                "
                            ></i>

                        </div>


                        <div>

                            <div
                                class="
                                    fw-semibold
                                "
                            >

                                <?= e(
                                    $wallet['name']
                                ) ?>

                            </div>


                            <small
                                class="
                                    text-muted
                                "
                            >

                                <?= e(
                                    $wallet['wallet_type']
                                ) ?>

                            </small>

                        </div>

                    </div>


                    <div
                        class="
                            wallet-balance
                        "
                    >

                        <?= dashboardMoney(
                            (float)
                            $wallet[
                                'current_balance'
                            ],
                            $wallet['currency']
                        ) ?>

                    </div>

                </div>


            <?php endforeach; ?>


        <?php endif; ?>

    </div>

</div>


<!-- =====================================================
     RECENT TRANSACTIONS
====================================================== -->

<div
    class="
        card
        border-0
        shadow-sm
        mb-4
    "
>

    <div
        class="
            card-body
        "
    >

        <div
            class="
                d-flex
                justify-content-between
                align-items-center
                mb-2
            "
        >

            <h5
                class="
                    fw-bold
                    mb-0
                "
            >

                Recent Transactions

            </h5>


            <a
                href="<?= BASE_URL ?>transactions/"
                class="
                    small
                    text-success
                    text-decoration-none
                "
            >

                View All

            </a>

        </div>


        <?php if (
            empty($recentTransactions)
        ): ?>


            <div
                class="
                    text-center
                    py-4
                "
            >

                <i
                    class="
                        bi
                        bi-receipt
                        fs-1
                        text-muted
                    "
                ></i>


                <p
                    class="
                        text-muted
                        mt-2
                        mb-0
                    "
                >

                    No transactions yet.

                </p>

            </div>


        <?php else: ?>


            <?php foreach (
                $recentTransactions
                as $transaction
            ): ?>


                <?php

                    $transactionType =
                        $transaction['type'];

                    $typeClass =
                        dashboardTypeClass(
                            $transactionType
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Transaction Description
                    |--------------------------------------------------------------------------
                    */

                    $description =
                        trim(
                            $transaction['description'] ?? ''
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Transfer Display
                    |--------------------------------------------------------------------------
                    |
                    | Example:
                    |
                    | BPI → GCash
                    |
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $transactionType === 'Transfer'
                    ) {

                        if (
                            !empty(
                                $transaction[
                                    'destination_wallet_name'
                                ]
                            )
                        ) {

                            $description =
                                $transaction[
                                    'wallet_name'
                                ]
                                .
                                ' → '
                                .
                                $transaction[
                                    'destination_wallet_name'
                                ];

                        } else {

                            $description =
                                'Wallet Transfer';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Default Description
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $description === ''
                    ) {

                        $description =
                            $transactionType .
                            ' transaction';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Transaction Icon
                    |--------------------------------------------------------------------------
                    */

                    $icon =
                        match (
                            $transactionType
                        ) {

                            'Income' =>
                                'bi-arrow-down-left',

                            'Expense' =>
                                'bi-arrow-up-right',

                            'Transfer' =>
                                'bi-arrow-left-right',

                            'Adjustment' =>
                                'bi-sliders',

                            default =>
                                'bi-receipt'

                        };

                    ?>


                <div
                    class="
                        transaction-item
                    "
                >


                    <div
                        class="
                            transaction-icon
                            <?= e(
                                $typeClass
                            ) ?>"
                    >

                        <i
                            class="
                                bi
                                <?= e(
                                    $icon
                                ) ?>"
                        ></i>

                    </div>


                    <div
                        class="
                            transaction-description
                        "
                    >

                        <div
                            class="
                                transaction-title
                            "
                        >

                            <?= e(
                                $description
                            ) ?>

                        </div>


                        <div
                            class="
                                transaction-meta
                            "
                        >

                            <?= e(
                                $transaction[
                                    'wallet_name'
                                ]
                            ) ?>


                            <?php if (
                                $transaction[
                                    'category_name'
                                ]
                            ): ?>

                                ·

                                <?= e(
                                    $transaction[
                                        'category_name'
                                    ]
                                ) ?>

                            <?php endif; ?>


                            ·


                            <?= date(
                                'M d, Y',
                                strtotime(
                                    $transaction[
                                        'transaction_date'
                                    ]
                                )
                            ) ?>

                        </div>

                    </div>


                    <div
                        class="
                            transaction-amount
                            <?= e(
                                $typeClass
                            ) ?>"
                    >

                        <?php if (
                            $transactionType ===
                            'Expense'
                        ): ?>

                            -

                        <?php elseif (
                            $transactionType ===
                            'Income'
                        ): ?>

                            +

                        <?php endif; ?>


                        ₱<?= number_format(
                            (float)
                            $transaction[
                                'amount'
                            ],
                            2
                        ) ?>

                    </div>


                </div>


            <?php endforeach; ?>


        <?php endif; ?>

    </div>

</div>

</main>

<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

<script
    src="<?= BASE_URL ?>assets/js/app.js"
></script>

</body>

</html>
