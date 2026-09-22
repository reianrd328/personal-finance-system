<?php

require_once __DIR__ .
    '/../app/config/config.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Start Session
|--------------------------------------------------------------------------
*/

if (
    session_status() ===
    PHP_SESSION_NONE
) {

    session_start();

}


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (
    !isset(
        $_SESSION['user_id']
    )
) {

    redirect(
        BASE_URL .
        'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$user =
    $_SESSION['user'] ?? [];


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
    Add Expense Category -
    <?= e(APP_NAME) ?>
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


<style>

body {

    background:
        #f8fafc;

    color:
        #1e293b;

}


/*
|--------------------------------------------------------------------------
| Desktop Content
|--------------------------------------------------------------------------
*/

.category-content {

    margin-left:
        250px;

    padding:
        30px;

}


/*
|--------------------------------------------------------------------------
| Page Header
|--------------------------------------------------------------------------
*/

.page-header {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    margin-bottom:
        24px;

}


.back-button {

    width:
        42px;

    height:
        42px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        10px;

    background:
        #ffffff;

    color:
        #1e293b;

    text-decoration:
        none;

    font-size:
        20px;

    box-shadow:
        0 2px 8px
        rgba(
            0,
            0,
            0,
            0.08
        );

}


.page-title-wrapper {

    flex:
        1;

}


.page-title {

    margin:
        0;

    font-size:
        26px;

    font-weight:
        700;

}


.page-subtitle {

    margin:
        4px 0 0;

    color:
        #64748b;

    font-size:
        14px;

}


/*
|--------------------------------------------------------------------------
| Form Card
|--------------------------------------------------------------------------
*/

.form-card {

    max-width:
        700px;

    border:
        0;

    border-radius:
        16px;

    box-shadow:
        0 4px 18px
        rgba(
            0,
            0,
            0,
            0.06
        );

}


.category-icon {

    width:
        52px;

    height:
        52px;

    border-radius:
        14px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #fee2e2;

    color:
        #dc2626;

    font-size:
        24px;

}


/*
|--------------------------------------------------------------------------
| Form Labels
|--------------------------------------------------------------------------
*/

.form-label {

    font-weight:
        600;

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (
    max-width: 767.98px
) {

    .category-content {

        margin-left:
            0;

        padding:
            16px;

    }


    .page-title {

        font-size:
            22px;

    }


    .form-card {

        width:
            100%;

    }

}

</style>

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
                "
            >

                <i class="bi bi-speedometer2 me-2"></i>

                Dashboard

            </a>


            <a
                href="<?= BASE_URL ?>wallets/"
                class="
                    list-group-item
                    list-group-item-action
                "
            >

                <i class="bi bi-wallet2 me-2"></i>

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
                    active
                "
            >

                <i class="bi bi-tags me-2"></i>

                Categories

            </a>


            <a
                href="<?= BASE_URL ?>reports/"
                class="
                    list-group-item
                    list-group-item-action
                "
            >

                <i class="bi bi-bar-chart me-2"></i>

                Reports

            </a>


            <a
                href="<?= BASE_URL ?>profile/"
                class="
                    list-group-item
                    list-group-item-action
                "
            >

                <i class="bi bi-person me-2"></i>

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

                    <i class="bi bi-people me-2"></i>

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

        <h5 class="mb-0 fw-bold">

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

            <i class="bi bi-speedometer2 me-2"></i>

            Dashboard

        </a>


        <a
            href="<?= BASE_URL ?>wallets/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i class="bi bi-wallet2 me-2"></i>

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
                active
            "
        >

            <i class="bi bi-tags me-2"></i>

            Categories

        </a>


        <a
            href="<?= BASE_URL ?>reports/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i class="bi bi-bar-chart me-2"></i>

            Reports

        </a>


        <a
            href="<?= BASE_URL ?>profile/"
            class="
                list-group-item
                list-group-item-action
            "
        >

            <i class="bi bi-person me-2"></i>

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

                <i class="bi bi-people me-2"></i>

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

<main class="category-content">


    <!-- Page Header -->

    <div class="page-header">


        <a
            href="<?= BASE_URL ?>categories/"
            class="back-button"
            aria-label="Back to Categories"
        >

            ←

        </a>


        <div class="page-title-wrapper">

            <h1 class="page-title">

                Add Expense Category

            </h1>


            <p class="page-subtitle">

                Create a custom category for your expenses.

            </p>

        </div>


    </div>


    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div class="card form-card">

        <div class="card-body p-4">


            <!-- Icon -->

            <div
                class="
                    category-icon
                    mb-3
                "
            >

                <i
                    class="
                        bi
                        bi-tags
                    "
                ></i>

            </div>


            <h5 class="fw-bold">

                Category Information

            </h5>


            <p
                class="
                    text-muted
                    small
                    mb-4
                "
            >

                Add a category that you can use when recording
                expense transactions.

            </p>


            <!-- Form -->

            <form
                method="POST"
                action="<?= BASE_URL ?>categories/store.php"
            >


                <!-- Category Name -->

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label"
                    >

                        Category Name

                    </label>


                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        maxlength="100"
                        placeholder="Example: Coffee"
                        required
                        autofocus
                    >


                    <div
                        class="
                            form-text
                        "
                    >

                        Enter a name that clearly describes
                        this expense category.

                    </div>

                </div>


                <!-- Description -->

                <div class="mb-4">

                    <label
                        for="description"
                        class="form-label"
                    >

                        Description

                    </label>


                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="4"
                        maxlength="255"
                        placeholder="Example: Coffee, drinks and beverages"
                    ></textarea>


                    <div
                        class="
                            form-text
                        "
                    >

                        Optional.

                    </div>

                </div>


                <!-- Hidden Type -->

                <input
                    type="hidden"
                    name="type"
                    value="Expense"
                >


                <!-- Buttons -->

                <div
                    class="
                        d-flex
                        gap-2
                    "
                >

                    <a
                        href="<?= BASE_URL ?>categories/"
                        class="
                            btn
                            btn-outline-secondary
                            flex-fill
                        "
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="
                            btn
                            btn-success
                            flex-fill
                        "
                    >

                        <i
                            class="
                                bi
                                bi-plus-lg
                                me-1
                            "
                        ></i>

                        Add Category

                    </button>

                </div>


            </form>


        </div>

    </div>


</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>