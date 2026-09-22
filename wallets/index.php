<?php

require_once __DIR__ .
    '/../app/middleware/auth.php';

require_once __DIR__ .
    '/../app/config/theme.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$user = currentUser();

$userId = currentUserId();

$flash = getFlash();


/*
|--------------------------------------------------------------------------
| Get Active Wallets
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Get Active Wallets With Current Balance
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        w.id,
        w.name,
        w.description,
        w.initial_balance,
        w.currency,
        w.status,

        wt.name AS wallet_type,

        COALESCE(
            SUM(
                CASE

                    WHEN t.type = 'Income'
                        THEN t.amount

                    WHEN t.type = 'Expense'
                        THEN -t.amount

                    WHEN t.type = 'Transfer'
                        THEN
                            CASE

                                WHEN tt.transaction_id = t.id
                                    THEN -t.amount

                                WHEN tt.destination_transaction_id = t.id
                                    THEN t.amount

                                ELSE 0

                            END

                    WHEN t.type = 'Adjustment'
                        THEN t.amount

                    ELSE 0

                END
            ),
            0
        ) AS transaction_balance

    FROM wallets w

    INNER JOIN wallet_types wt
        ON w.wallet_type_id = wt.id

    LEFT JOIN transactions t
        ON t.wallet_id = w.id
        AND t.user_id = :transaction_user_id

    LEFT JOIN transaction_transfers tt
        ON (
            tt.transaction_id = t.id
            OR
            tt.destination_transaction_id = t.id
        )

    WHERE w.user_id = :wallet_user_id

    AND w.status = 'Active'

    GROUP BY
        w.id,
        w.name,
        w.description,
        w.initial_balance,
        w.currency,
        w.status,
        wt.name

    ORDER BY
        w.created_at DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':transaction_user_id' => $userId,
    ':wallet_user_id' => $userId
]);

$wallets = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Calculate Total Initial Balance
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Calculate Current Wallet Balances
|--------------------------------------------------------------------------
*/

$totalBalance = 0;

foreach ($wallets as &$wallet) {

    $wallet['current_balance'] =
        (float) $wallet['initial_balance']
        +
        (float) $wallet['transaction_balance'];

    $totalBalance +=
        $wallet['current_balance'];
}

unset($wallet);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width,
        initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="<?= PRIMARY_COLOR ?>"
    >

    <title>
        My Wallets -
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
        href="<?= BASE_URL ?>assets/css/style.css"
    >

</head>

<style>

.wallets-content {
    margin-left: 250px;
    padding: 24px;
}

@media (max-width: 767.98px) {
    .wallets-content {
        margin-left: 0;
        padding: 14px;
    }
}

</style>


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
                    active
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
                active
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
        wallets-content
    "
>


    <!-- Header -->

    <div
        class="d-flex
        flex-column
        flex-sm-row
        justify-content-between
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

                My Wallets

            </h2>


            <p
                class="text-muted
                mb-0"
            >

                Manage your bank accounts,
                e-wallets, cash, and other
                money sources.

            </p>

        </div>


        <div
            class="d-flex
            align-items-start"
        >

            <a
                href="<?= BASE_URL ?>wallets/create.php"
                class="btn btn-success"
            >

                <i
                    class="bi
                    bi-wallet2
                    me-1"
                ></i>

                Add Wallet

            </a>

        </div>

    </div>



    <!-- Flash Message -->

    <?php if ($flash): ?>

        <div
            class="alert
            alert-<?= e(
                $flash['type']
            ) ?>
            alert-dismissible
            fade
            show"
        >

            <?= e(
                $flash['message']
            ) ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <!-- Total Balance -->

    <div
        class="card
        border-0
        shadow-sm
        mb-4"
    >

        <div
            class="card-body
            p-4"
        >

            <div
                class="text-muted
                small"
            >

                Total Money

            </div>


            <h1
                class="fw-bold
                mt-2
                mb-1"
            >

                <?= e(
                    formatMoney(
                        $totalBalance
                    )
                ) ?>

            </h1>


            <div
                class="text-muted"
            >

                Across
                <?= count($wallets) ?>
                active wallet(s)

            </div>

        </div>

    </div>



    <!-- Wallets -->

    <?php if (!$wallets): ?>


        <div
            class="card
            border-0
            shadow-sm"
        >

            <div
                class="card-body
                text-center
                p-5"
            >

                <div
                    style="font-size: 50px;"
                >

                    💰

                </div>


                <h4
                    class="fw-bold
                    mt-3"
                >

                    No wallets yet

                </h4>


                <p
                    class="text-muted"
                >

                    Add your first wallet
                    to start tracking
                    your money.

                </p>


                <a
                    href="<?= BASE_URL ?>wallets/create.php"
                    class="btn btn-success"
                >

                    <i
                        class="bi
                        bi-plus-circle
                        me-1"
                    ></i>

                    Add Your First Wallet

                </a>

            </div>

        </div>


    <?php else: ?>


        <div
            class="row
            g-3"
        >


            <?php foreach (
                $wallets
                as $wallet
            ): ?>


                <div
                    class="col-12
                    col-sm-6
                    col-lg-4"
                >

                    <div
                        class="card
                        border-0
                        shadow-sm
                        h-100"
                    >

                        <div
                            class="card-body
                            p-4"
                        >


                            <!-- Wallet Icon -->

                            <div
                                class="d-flex
                                justify-content-between
                                align-items-start"
                            >

                                <div
                                    class="rounded-circle
                                    bg-success-subtle
                                    text-success
                                    d-flex
                                    align-items-center
                                    justify-content-center"
                                    style="
                                        width: 48px;
                                        height: 48px;
                                    "
                                >

                                    <i
                                        class="bi
                                        bi-wallet2
                                        fs-4"
                                    ></i>

                                </div>


                                <span
                                    class="badge
                                    bg-success-subtle
                                    text-success"
                                >

                                    Active

                                </span>

                            </div>


                            <!-- Wallet Name -->

                            <h5 
                                class="fw-bold mt-4 mb-1">

                                <a
                                    href="<?= BASE_URL ?>wallets/view.php?id=<?= (int) $wallet['id'] ?>"
                                    class="text-decoration-none text-dark"
                                >

                                    <?= e($wallet['name']) ?>

                                </a>

                            </h5>


                            <!-- Wallet Type -->

                            <div
                                class="text-muted
                                small
                                mb-3"
                            >

                                <i
                                    class="bi
                                    bi-tag
                                    me-1"
                                ></i>

                                <?= e(
                                    $wallet[
                                        'wallet_type'
                                    ]
                                ) ?>

                            </div>


                            <!-- Balance -->

                            <div
                                class="mb-3"
                            >

                                <div
                                    class="text-muted
                                    small"
                                >

                                    Balance

                                </div>


                                <div
                                    class="fs-3
                                    fw-bold"
                                >

                                    <?= e(
                                        formatMoney(
                                            $wallet[
                                                'current_balance'
                                            ]
                                        )
                                    ) ?>

                                </div>

                            </div>


                            <?php if (
                                !empty(
                                    $wallet[
                                        'description'
                                    ]
                                )
                            ): ?>

                                <p
                                    class="text-muted
                                    small"
                                >

                                    <?= e(
                                        $wallet[
                                            'description'
                                        ]
                                    ) ?>

                                </p>

                            <?php endif; ?>


                            <!-- Actions -->

                            <div
                                class="d-flex
                                gap-2
                                mt-3"
                            >

                                <a
                                    href="<?= BASE_URL ?>wallets/view.php?id=<?= (int) $wallet['id'] ?>"
                                    class="btn
                                    btn-outline-primary
                                    btn-sm
                                    flex-grow-1"
                                >

                                    <i
                                        class="bi
                                        bi-eye
                                        me-1"
                                    ></i>

                                    View

                                </a>

                                <a
                                    href="<?= BASE_URL ?>wallets/edit.php?id=<?= (int) $wallet['id'] ?>"
                                    class="btn
                                    btn-outline-secondary
                                    btn-sm
                                    flex-grow-1"
                                >

                                    <i
                                        class="bi
                                        bi-pencil
                                        me-1"
                                    ></i>

                                    Edit

                                </a>


                                <a
                                    href="<?= BASE_URL ?>wallets/archive.php?id=<?= (int) $wallet['id'] ?>"
                                    class="btn
                                    btn-outline-danger
                                    btn-sm
                                    flex-grow-1"
                                    onclick="
                                        return confirm(
                                            'Archive this wallet?'
                                        );
                                    "
                                >

                                    <i
                                        class="bi
                                        bi-archive
                                        me-1"
                                    ></i>

                                    Archive

                                </a>

                            </div>


                        </div>

                    </div>

                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>