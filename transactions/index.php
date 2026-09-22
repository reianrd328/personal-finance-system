<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$isAdmin =
    isset($user['role']) &&
    $user['role'] === 'Admin';


/*
|--------------------------------------------------------------------------
| Get Transactions
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        t.id,
        t.wallet_id,
        t.category_id,
        t.type,
        t.amount,
        t.transaction_date,
        t.description,
        t.notes,

        w.name AS wallet_name,

        c.name AS category_name

    FROM transactions t

    INNER JOIN wallets w
        ON t.wallet_id = w.id

    LEFT JOIN categories c
        ON t.category_id = c.id

    WHERE t.user_id = :user_id

    ORDER BY
        t.transaction_date DESC,
        t.id DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':user_id' => $userId
]);

$transactions = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Count Transactions
|--------------------------------------------------------------------------
*/

$totalTransactions =
    count($transactions);

?>

<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Transactions</title>

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

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>assets/css/style_transaction_index.css"
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
                    active
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
                active
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
        transactions-content
    "
>


<!-- Header -->

<div class="page-header">

    <a
        href="<?= BASE_URL ?>dashboard/"
        class="back-button"
        aria-label="Back to Dashboard"
    >
        ←
    </a>


    <div class="page-title-wrapper">

        <h1 class="page-title">
            Transactions
        </h1>

        <p class="transaction-count">

            <?= $totalTransactions ?>

            transaction<?= $totalTransactions === 1 ? '' : 's' ?>

        </p>

    </div>


    <a
        href="<?= BASE_URL ?>transactions/create.php"
        class="add-button"
    >

        +

        <span>
            Add Transaction
        </span>

    </a>

</div>


<?php if (empty($transactions)): ?>


    <!-- Empty State -->

    <div class="empty-state">

        <div class="empty-icon">
            💰
        </div>

        <h2>
            No transactions yet
        </h2>

        <p>
            Start recording your income and expenses.
        </p>

        <a
            href="<?= BASE_URL ?>transactions/create.php"
            class="empty-add-button"
        >
            Add Your First Transaction
        </a>

    </div>


<?php else: ?>


    <!-- =====================================================
         MOBILE VIEW
    ====================================================== -->

    <div class="transaction-list">

        <?php foreach ($transactions as $transaction): ?>

            <?php

            $type =
                $transaction['type'];

            $amountClass =
                'amount-' .
                strtolower($type);

            $badgeClass =
                'badge-' .
                strtolower($type);

            $description =
                $transaction['description'];

            if (
                $description === null ||
                trim($description) === ''
            ) {

                $description =
                    ucfirst($type) .
                    ' transaction';

            }

            ?>


            <div class="transaction-card">


                <div class="transaction-top">


                    <div class="transaction-main">

                        <h2 class="transaction-description">

                            <?= htmlspecialchars(
                                $description
                            ) ?>

                        </h2>


                        <div class="transaction-category">

                            <?php if (
                                $transaction['category_name']
                            ): ?>

                                <?= htmlspecialchars(
                                    $transaction['category_name']
                                ) ?>

                            <?php else: ?>

                                No category

                            <?php endif; ?>

                        </div>

                    </div>


                    <div
                        class="transaction-amount
                        <?= htmlspecialchars(
                            $amountClass
                        ) ?>"
                    >

                        <?= $type === 'Expense'
                            ? '-'
                            : ''
                        ?>

                        ₱<?= number_format(
                            (float)
                            $transaction['amount'],
                            2
                        ) ?>

                    </div>


                </div>


                <div class="transaction-details">


                    <div>

                        <span class="detail-label">
                            Type
                        </span>

                        <span
                            class="type-badge
                            <?= htmlspecialchars(
                                $badgeClass
                            ) ?>"
                        >

                            <?= htmlspecialchars(
                                $type
                            ) ?>

                        </span>

                    </div>


                    <div>

                        <span class="detail-label">
                            Wallet
                        </span>

                        <span class="detail-value">

                            <?= htmlspecialchars(
                                $transaction['wallet_name']
                            ) ?>

                        </span>

                    </div>


                    <div>

                        <span class="detail-label">
                            Date
                        </span>

                        <span class="detail-value">

                            <?= date(
                                'M d, Y',
                                strtotime(
                                    $transaction[
                                        'transaction_date'
                                    ]
                                )
                            ) ?>

                        </span>

                    </div>


                    <div>

                        <span class="detail-label">
                            Transaction ID
                        </span>

                        <span class="detail-value">

                            #<?= (int)
                                $transaction['id'] ?>

                        </span>

                    </div>


                </div>


                <?php if ($isAdmin): ?>

                    <div class="transaction-actions">

                        <form
                            action="<?= BASE_URL ?>transactions/delete.php"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');"
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $transaction['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-danger"
                            >
                                <i class="bi bi-trash"></i>
                                Delete
                            </button>

                        </form>

                    </div>

                <?php endif; ?>


            </div>


        <?php endforeach; ?>

    </div>


    <!-- =====================================================
         DESKTOP VIEW
    ====================================================== -->

    <div class="desktop-table">

        <table>

            <thead>

                <tr>

                    <th>
                        Date
                    </th>

                    <th>
                        Type
                    </th>

                    <th>
                        Description
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Wallet
                    </th>

                    <th style="text-align:right;">
                        Amount
                    </th>

                    <?php if ($isAdmin): ?>
                        <th style="text-align:center;">
                            Actions
                        </th>
                    <?php endif; ?>

                </tr>

            </thead>


            <tbody>

                <?php foreach (
                    $transactions
                    as $transaction
                ): ?>

                    <?php

                    $type =
                        $transaction['type'];

                    $badgeClass =
                        'badge-' .
                        strtolower($type);

                    $description =
                        $transaction['description'];

                    if (
                        !$description
                    ) {

                        $description =
                            ucfirst($type) .
                            ' transaction';

                    }

                    ?>


                    <tr>

                        <td class="desktop-date">

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

                            <span
                                class="type-badge
                                <?= htmlspecialchars(
                                    $badgeClass
                                ) ?>"
                            >

                                <?= htmlspecialchars(
                                    $type
                                ) ?>

                            </span>

                        </td>


                        <td class="desktop-description">

                            <?= htmlspecialchars(
                                $description
                            ) ?>

                        </td>


                        <td>

                            <?= $transaction[
                                'category_name'
                            ]
                                ? htmlspecialchars(
                                    $transaction[
                                        'category_name'
                                    ]
                                )
                                : '—'
                            ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $transaction[
                                    'wallet_name'
                                ]
                            ) ?>

                        </td>


                        <td
                            class="desktop-amount
                            <?= $type === 'Income'
                                ? 'amount-income'
                                : (
                                    $type === 'Expense'
                                        ? 'amount-expense'
                                        : (
                                            $type === 'Transfer'
                                                ? 'amount-transfer'
                                                : 'amount-adjustment'
                                        )
                                )
                            ?>"
                        >

                            <?= $type === 'Expense'
                                ? '-'
                                : ''
                            ?>

                            ₱<?= number_format(
                                (float)
                                $transaction['amount'],
                                2
                            ) ?>

                        </td>


                        <?php if ($isAdmin): ?>

                            <td style="text-align:center;">

                                <form
                                    action="<?= BASE_URL ?>transactions/delete.php"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');"
                                    class="d-inline"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $transaction['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Delete transaction"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>

                            </td>

                        <?php endif; ?>

                    </tr>


                <?php endforeach; ?>

            </tbody>

        </table>

    </div>


<?php endif; ?>


</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
