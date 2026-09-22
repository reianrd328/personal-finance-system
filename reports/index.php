<?php

require_once __DIR__ . '/../app/middleware/auth.php';
require_once __DIR__ . '/../app/config/theme.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$user = currentUser();

$userId = currentUserId();

/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';

$startDate = trim($startDate);
$endDate   = trim($endDate);


/*
|--------------------------------------------------------------------------
| Validate Dates
|--------------------------------------------------------------------------
*/

if (
    $startDate !== ''
    &&
    !DateTime::createFromFormat(
        'Y-m-d',
        $startDate
    )
) {

    $startDate = '';

}


if (
    $endDate !== ''
    &&
    !DateTime::createFromFormat(
        'Y-m-d',
        $endDate
    )
) {

    $endDate = '';

}


/*
|--------------------------------------------------------------------------
| Date Validation
|--------------------------------------------------------------------------
*/

if (
    $startDate !== ''
    &&
    $endDate !== ''
    &&
    $startDate > $endDate
) {

    [$startDate, $endDate] =
        [$endDate, $startDate];

}

/*
|--------------------------------------------------------------------------
| Get Active Wallets
|--------------------------------------------------------------------------
|
| Balance calculation:
|
| Initial Balance
| + Income
| - Expense
| + Adjustment
| - Transfer Sent
| + Transfer Received
|
|--------------------------------------------------------------------------
*/

$walletSql = "
    SELECT

        w.id,

        w.name,

        w.initial_balance,

        w.currency,

        wt.name AS wallet_type,

        (
            w.initial_balance

            /* Income */

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

            /* Expense */

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

            /* Adjustment */

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

            /* Transfer Sent */

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

            /* Transfer Received */

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
| Total Balance
|--------------------------------------------------------------------------
*/

$totalBalance = 0;

foreach ($wallets as $wallet) {

    $totalBalance +=
        (float) $wallet['current_balance'];

}


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
        )

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Income'
";


$incomeParams = [
    ':user_id' => $userId
];


/*
|--------------------------------------------------------------------------
| Apply Date Filter
|--------------------------------------------------------------------------
*/

if ($startDate !== '') {

    $incomeSql .= "
        AND transaction_date >= :start_date
    ";

    $incomeParams[':start_date'] =
        $startDate;

}


if ($endDate !== '') {

    $incomeSql .= "
        AND transaction_date <= :end_date
    ";

    $incomeParams[':end_date'] =
        $endDate;

}


$incomeStmt =
    $pdo->prepare($incomeSql);


$incomeStmt->execute(
    $incomeParams
);


$totalIncome =
    (float) $incomeStmt->fetchColumn();


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
        )

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Expense'
";


$expenseParams = [
    ':user_id' => $userId
];


/*
|--------------------------------------------------------------------------
| Apply Date Filter
|--------------------------------------------------------------------------
*/

if ($startDate !== '') {

    $expenseSql .= "
        AND transaction_date >= :start_date
    ";

    $expenseParams[':start_date'] =
        $startDate;

}


if ($endDate !== '') {

    $expenseSql .= "
        AND transaction_date <= :end_date
    ";

    $expenseParams[':end_date'] =
        $endDate;

}


$expenseStmt =
    $pdo->prepare($expenseSql);

$expenseStmt->execute(
    $expenseParams
);

$totalExpense =
    (float) $expenseStmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Total Adjustments
|--------------------------------------------------------------------------
*/

$adjustmentSql = "
    SELECT
        COALESCE(
            SUM(amount),
            0
        )

    FROM transactions

    WHERE user_id = :user_id

      AND type = 'Adjustment'
";


$adjustmentParams = [
    ':user_id' => $userId
];


/*
|--------------------------------------------------------------------------
| Apply Date Filter
|--------------------------------------------------------------------------
*/

if ($startDate !== '') {

    $adjustmentSql .= "
        AND transaction_date >= :start_date
    ";

    $adjustmentParams[':start_date'] =
        $startDate;

}


if ($endDate !== '') {

    $adjustmentSql .= "
        AND transaction_date <= :end_date
    ";

    $adjustmentParams[':end_date'] =
        $endDate;

}


$adjustmentStmt =
    $pdo->prepare($adjustmentSql);

$adjustmentStmt->execute(
    $adjustmentParams
);

$totalAdjustment =
    (float) $adjustmentStmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Net Cash Flow
|--------------------------------------------------------------------------
|
| Income
| - Expense
| + Adjustment
|
| Transfers are excluded because they only
| move money between the user's own wallets.
|
|--------------------------------------------------------------------------
*/

$netCashFlow =
    $totalIncome
    -
    $totalExpense
    +
    $totalAdjustment;


/*
|--------------------------------------------------------------------------
| Recent Transactions
|--------------------------------------------------------------------------
*/

$recentSql = "
    SELECT

        t.id,

        t.type,

        t.amount,

        t.transaction_date,

        t.description,

        w.name AS wallet_name,

        c.name AS category_name

    FROM transactions t

    INNER JOIN wallets w
        ON t.wallet_id = w.id

    LEFT JOIN categories c
        ON t.category_id = c.id

    WHERE t.user_id = :user_id
";


$recentParams = [
    ':user_id' => $userId
];


/*
|--------------------------------------------------------------------------
| Apply Start Date
|--------------------------------------------------------------------------
*/

if ($startDate !== '') {

    $recentSql .= "
        AND t.transaction_date >= :start_date
    ";

    $recentParams[':start_date'] =
        $startDate;

}


/*
|--------------------------------------------------------------------------
| Apply End Date
|--------------------------------------------------------------------------
*/

if ($endDate !== '') {

    $recentSql .= "
        AND t.transaction_date <= :end_date
    ";

    $recentParams[':end_date'] =
        $endDate;

}


/*
|--------------------------------------------------------------------------
| Sort and Limit
|--------------------------------------------------------------------------
*/

$recentSql .= "

    ORDER BY

        t.transaction_date DESC,

        t.id DESC

    LIMIT 10

";


$recentStmt =
    $pdo->prepare($recentSql);


$recentStmt->execute(
    $recentParams
);


$recentTransactions =
    $recentStmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Money Formatter
|--------------------------------------------------------------------------
*/

function reportMoney(
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


/*
|--------------------------------------------------------------------------
| Transaction Type Class
|--------------------------------------------------------------------------
*/

function reportTypeClass(
    string $type
): string {

    switch ($type) {

        case 'Income':
            return 'text-success';

        case 'Expense':
            return 'text-danger';

        case 'Transfer':
            return 'text-primary';

        case 'Adjustment':
            return 'text-purple';

        default:
            return 'text-dark';

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
        Reports -
        <?= e(APP_NAME) ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/style_reports_index.css"
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
            aria-controls="mobileMenu"
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
    aria-labelledby="mobileMenuLabel"
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
            id="mobileMenuLabel"
        >

            💰 <?= e(APP_NAME) ?>

        </h5>


        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            aria-label="Close"
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
                    active
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
                active
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
        reports-content
    "
>


        <!-- Header -->

        <div
            class="d-flex
            flex-column
            flex-md-row
            justify-content-between
            align-items-md-center
            gap-3
            mb-4"
        >

            <div>

                <a
                    href="<?= BASE_URL ?>dashboard/"
                    class="btn
                    btn-outline-secondary
                    btn-sm
                    mb-3"
                >

                    <i
                        class="bi
                        bi-arrow-left
                        me-1"
                    ></i>

                    Back to Dashboard

                </a>


                <h2
                    class="fw-bold
                    mb-1"
                >

                    Financial Reports

                </h2>


                <p
                    class="text-muted
                    mb-0"
                >

                    Overview of your financial activity.

                </p>

            </div>

        </div>


<!-- =========================================================
     DATE FILTER
========================================================= -->

<div
    class="card
    report-card
    mb-4"
>

    <div
        class="card-body"
    >

        <form
            method="GET"
            action="<?= BASE_URL ?>reports/generate.php"
            class="row
            g-3
            align-items-end"
        >

            <!-- Start Date -->

            <div
                class="col-12
                col-md-4"
            >

                <label
                    for="start_date"
                    class="form-label
                    fw-semibold"
                >

                    Start Date

                </label>


                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    class="form-control"
                    value="<?= e($startDate) ?>"
                >

            </div>


            <!-- End Date -->

            <div
                class="col-12
                col-md-4"
            >

                <label
                    for="end_date"
                    class="form-label
                    fw-semibold"
                >

                    End Date

                </label>


                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    class="form-control"
                    value="<?= e($endDate) ?>"
                >

            </div>


            <!-- Buttons -->

            <div
                class="col-12
                col-md-4
                d-flex
                gap-2"
            >

                <button
                    type="submit"
                    class="btn
                    btn-success
                    flex-grow-1"
                >

                    <i
                        class="bi
                        bi-file-earmark-bar-graph
                        me-1"
                    ></i>

                    Generate Report

                </button>


                <a
                    href="<?= BASE_URL ?>reports/"
                    class="btn
                    btn-outline-secondary"
                >

                    <i
                        class="bi
                        bi-arrow-counterclockwise"
                    ></i>

                    Reset

                </a>

            </div>

        </form>


        <?php if (
            $startDate !== ''
            ||
            $endDate !== ''
        ): ?>

            <div
                class="mt-3
                small
                text-muted"
            >

                <i
                    class="bi
                    bi-info-circle
                    me-1"
                ></i>

                Showing report data for:

                <strong>

                    <?= $startDate !== ''
                        ? date(
                            'M d, Y',
                            strtotime($startDate)
                        )
                        : 'Beginning'
                    ?>

                    -

                    <?= $endDate !== ''
                        ? date(
                            'M d, Y',
                            strtotime($endDate)
                        )
                        : 'Present'
                    ?>

                </strong>

            </div>

        <?php endif; ?>

    </div>

</div>



        <!-- Summary -->

        <div
            class="row
            g-3
            mb-4"
        >


            <!-- Balance -->

            <div
                class="col-12
                col-md-6
                col-xl-3"
            >

                <div
                    class="card
                    report-card
                    h-100"
                >

                    <div
                        class="card-body"
                    >

                        <div
                            class="summary-icon
                            icon-balance
                            mb-3"
                        >

                            <i
                                class="bi
                                bi-wallet2"
                            ></i>

                        </div>


                        <small
                            class="text-muted"
                        >

                            Current Balance

                        </small>


                        <h4
                            class="fw-bold
                            mt-1
                            mb-0"
                        >

                            <?= reportMoney(
                                $totalBalance
                            ) ?>

                        </h4>

                    </div>

                </div>

            </div>


            <!-- Income -->

            <div
                class="col-6
                col-md-6
                col-xl-3"
            >

                <div
                    class="card
                    report-card
                    h-100"
                >

                    <div
                        class="card-body"
                    >

                        <div
                            class="summary-icon
                            icon-income
                            mb-3"
                        >

                            <i
                                class="bi
                                bi-arrow-down-left"
                            ></i>

                        </div>


                        <small
                            class="text-muted"
                        >

                            Total Income

                        </small>


                        <h4
                            class="fw-bold
                            text-success
                            mt-1
                            mb-0"
                        >

                            <?= reportMoney(
                                $totalIncome
                            ) ?>

                        </h4>

                    </div>

                </div>

            </div>


            <!-- Expenses -->

            <div
                class="col-6
                col-md-6
                col-xl-3"
            >

                <div
                    class="card
                    report-card
                    h-100"
                >

                    <div
                        class="card-body"
                    >

                        <div
                            class="summary-icon
                            icon-expense
                            mb-3"
                        >

                            <i
                                class="bi
                                bi-arrow-up-right"
                            ></i>

                        </div>


                        <small
                            class="text-muted"
                        >

                            Total Expenses

                        </small>


                        <h4
                            class="fw-bold
                            text-danger
                            mt-1
                            mb-0"
                        >

                            <?= reportMoney(
                                $totalExpense
                            ) ?>

                        </h4>

                    </div>

                </div>

            </div>


            <!-- Adjustments -->

            <div
                class="col-6
                col-md-6
                col-xl-3"
            >

                <div
                    class="card
                    report-card
                    h-100"
                >

                    <div
                        class="card-body"
                    >

                        <div
                            class="summary-icon
                            icon-adjustment
                            mb-3"
                        >

                            <i
                                class="bi
                                bi-sliders"
                            ></i>

                        </div>


                        <small
                            class="text-muted"
                        >

                            Adjustments

                        </small>


                        <h4
                            class="fw-bold
                            text-purple
                            mt-1
                            mb-0"
                        >

                            <?= reportMoney(
                                $totalAdjustment
                            ) ?>

                        </h4>

                    </div>

                </div>

            </div>

        </div>


        <!-- Net Cash Flow -->

        <div
            class="card
            report-card
            mb-4"
        >

            <div
                class="card-body
                p-4"
            >

                <div
                    class="d-flex
                    align-items-center
                    gap-3"
                >

                    <div
                        class="summary-icon
                        icon-flow"
                    >

                        <i
                            class="bi
                            bi-graph-up-arrow"
                        ></i>

                    </div>


                    <div>

                        <div
                            class="text-muted
                            small"
                        >

                            Net Cash Flow

                        </div>


                        <div
                            class="fs-3
                            fw-bold
                            <?= $netCashFlow >= 0
                                ? 'text-success'
                                : 'text-danger'
                            ?>"
                        >

                            <?= reportMoney(
                                $netCashFlow
                            ) ?>

                        </div>


                        <div
                            class="text-muted
                            small"
                        >

                            Income − Expenses + Adjustments

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Wallet Breakdown -->

        <div
            class="card
            report-card
            mb-4"
        >

            <div
                class="card-body"
            >

                <h5
                    class="fw-bold
                    mb-3"
                >

                    Wallet Breakdown

                </h5>


                <?php if (
                    empty($wallets)
                ): ?>

                    <p
                        class="text-muted
                        mb-0"
                    >

                        No active wallets found.

                    </p>

                <?php else: ?>

                    <div
                        class="table-responsive"
                    >

                        <table
                            class="table
                            table-hover
                            mb-0"
                        >

                            <thead>

                                <tr>

                                    <th>
                                        Wallet
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th
                                        class="text-end"
                                    >
                                        Current Balance
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach (
                                    $wallets
                                    as $wallet
                                ): ?>

                                    <tr>

                                        <td>

                                            <strong>
                                                <?= e(
                                                    $wallet['name']
                                                ) ?>
                                            </strong>

                                        </td>


                                        <td>

                                            <span
                                                class="text-muted"
                                            >

                                                <?= e(
                                                    $wallet[
                                                        'wallet_type'
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td
                                            class="text-end
                                            fw-bold"
                                        >

                                            <?= reportMoney(
                                                (float)
                                                $wallet[
                                                    'current_balance'
                                                ],
                                                $wallet[
                                                    'currency'
                                                ]
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- Recent Transactions -->

        <div
            class="card
            report-card
            mb-4"
        >

            <div
                class="card-body"
            >

                <div
                    class="d-flex
                    justify-content-between
                    align-items-center
                    mb-3"
                >

                    <h5
                        class="fw-bold
                        mb-0"
                    >

                        Recent Transactions

                    </h5>


                    <a
                        href="<?= BASE_URL ?>transactions/"
                        class="small
                        text-success
                        text-decoration-none"
                    >

                        View All

                    </a>

                </div>


                <?php if (
                    empty(
                        $recentTransactions
                    )
                ): ?>

                    <p
                        class="text-muted
                        mb-0"
                    >

                        No transactions found.

                    </p>

                <?php else: ?>

                    <div
                        class="table-responsive"
                    >

                        <table
                            class="table
                            table-hover
                            mb-0"
                        >

                            <thead>

                                <tr>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Wallet
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th
                                        class="text-end"
                                    >
                                        Amount
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach (
                                    $recentTransactions
                                    as $transaction
                                ): ?>

                                    <tr>

                                        <td>

                                            <?= date(
                                                'M d, Y',
                                                strtotime(
                                                    $transaction[
                                                        'transaction_date'
                                                    ]
                                                )
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $transaction[
                                                    'description'
                                                ] ?: (
                                                    $transaction[
                                                        'type'
                                                    ]
                                                    .
                                                    ' transaction'
                                                )
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $transaction[
                                                    'wallet_name'
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <span
                                                class="<?= e(
                                                    reportTypeClass(
                                                        $transaction[
                                                            'type'
                                                        ]
                                                    )
                                                ) ?>"
                                            >

                                                <?= e(
                                                    $transaction[
                                                        'type'
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td
                                            class="text-end
                                            fw-bold"
                                        >

                                            <?php if (
                                                $transaction[
                                                    'type'
                                                ] === 'Income'
                                            ): ?>

                                                <span
                                                    class="text-success"
                                                >
                                                    +
                                                </span>

                                            <?php elseif (
                                                $transaction[
                                                    'type'
                                                ] === 'Expense'
                                            ): ?>

                                                <span
                                                    class="text-danger"
                                                >
                                                    -
                                                </span>

                                            <?php endif; ?>


                                            <?= reportMoney(
                                                abs(
                                                    (float)
                                                    $transaction[
                                                        'amount'
                                                    ]
                                                )
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </div>


    </main>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
