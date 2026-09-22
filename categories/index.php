<?php

require_once __DIR__ .
    '/../app/config/config.php';

require_once __DIR__ .
    '/../app/config/database.php';

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


$userId =
    (int)
    $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Expense Categories
|--------------------------------------------------------------------------
|
| Show:
|
| 1. System/default categories
|    user_id IS NULL
|
| 2. Current user's custom categories
|    user_id = current user
|
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        id,
        user_id,
        name,
        description,
        status,
        created_at

    FROM categories

    WHERE type = 'Expense'

      AND status = 'Active'

      AND (
            user_id IS NULL
            OR user_id = :user_id
      )

    ORDER BY

        user_id IS NOT NULL ASC,
        name ASC

";


$stmt =
    $pdo->prepare($sql);


$stmt->execute([

    ':user_id' =>
        $userId

]);


$categories =
    $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Count Categories
|--------------------------------------------------------------------------
*/

$totalCategories =
    count($categories);

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
    Expense Categories - <?= e(APP_NAME) ?>
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
    href="<?= BASE_URL ?>assets/css/style_categories_index.css"
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
     MOBILE MENU
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

                <i class="bi bi-arrow-left-right me-2"></i>

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

                <i class="bi bi-box-arrow-right me-2"></i>

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
        width:250px;
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

            <i class="bi bi-arrow-left-right me-2"></i>

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

            <i class="bi bi-box-arrow-right me-1"></i>

            Logout

        </a>

    </div>

</div>


<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main class="categories-content">


    <!-- Header -->

    <div class="page-header">


        <a
            href="<?= BASE_URL ?>dashboard/"
            class="back-button"
        >

            ←

        </a>


        <div class="page-title-wrapper">

            <h1 class="page-title">

                Expense Categories

            </h1>


            <p class="page-subtitle">

                <?= $totalCategories ?>

                active expense categor<?= $totalCategories === 1 ? 'y' : 'ies' ?>

            </p>

        </div>


        <a
            href="<?= BASE_URL ?>categories/create.php"
            class="
                btn
                btn-success
                add-button
            "
        >

            <i class="bi bi-plus-lg"></i>

            <span class="add-button-text">

                Add Category

            </span>

        </a>


    </div>


    <?php if (
        empty($categories)
    ): ?>


        <!-- Empty State -->

        <div class="empty-state">

            <div class="empty-icon">

                <i class="bi bi-tags"></i>

            </div>


            <h4>

                No Expense Categories

            </h4>


            <p class="text-muted">

                Create your first expense category.

            </p>


            <a
                href="<?= BASE_URL ?>categories/create.php"
                class="btn btn-success"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Add Category

            </a>

        </div>


    <?php else: ?>


        <!-- Category List -->

        <?php foreach (
            $categories
            as $category
        ): ?>


            <div
                class="
                    card
                    category-card
                "
            >

                <div class="card-body">


                    <div
                        class="
                            d-flex
                            align-items-center
                            gap-3
                        "
                    >


                        <div
                            class="category-icon"
                        >

                            <i
                                class="
                                    bi
                                    bi-receipt
                                "
                            ></i>

                        </div>


                        <div
                            class="
                                flex-grow-1
                                min-width-0
                            "
                        >

                            <div
                                class="
                                    category-name
                                "
                            >

                                <?= e(
                                    $category['name']
                                ) ?>


                                <?php if (
                                    $category['user_id']
                                    === null
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            system-badge
                                            ms-1
                                        "
                                    >

                                        System

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="
                                            badge
                                            custom-badge
                                            ms-1
                                        "
                                    >

                                        My Category

                                    </span>

                                <?php endif; ?>

                            </div>


                            <div
                                class="
                                    category-description
                                    mt-1
                                "
                            >

                                <?= e(
                                    $category[
                                        'description'
                                    ]
                                    ?: 'No description'
                                ) ?>

                            </div>

                        </div>

                            <!-- Category Actions -->

                                <?php if (
                                    $category['user_id'] !== null
                                ): ?>

                                    <div
                                        class="
                                            d-flex
                                            align-items-center
                                            gap-2
                                            ms-2
                                            flex-shrink-0
                                        "
                                    >

                                        <!-- Edit -->

                                        <a
                                            href="<?= BASE_URL ?>categories/edit.php?id=<?= (int) $category['id'] ?>"
                                            class="
                                                btn
                                                btn-outline-primary
                                                btn-sm
                                            "
                                            title="Edit Category"
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-pencil
                                                "
                                            ></i>

                                            <span class="d-none d-sm-inline">
                                                Edit
                                            </span>

                                        </a>


                                        <!-- Deactivate -->

                                        <a
                                            href="<?= BASE_URL ?>categories/delete.php?id=<?= (int) $category['id'] ?>"
                                            class="
                                                btn
                                                btn-outline-danger
                                                btn-sm
                                            "
                                            title="Deactivate Category"
                                            onclick="
                                                return confirm(
                                                    'Are you sure you want to deactivate this category?'
                                                );
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-trash
                                                "
                                            ></i>

                                            <span class="d-none d-sm-inline">
                                                Deactivate
                                            </span>

                                        </a>

                                    </div>

                                <?php endif; ?>


                    </div>


                </div>

            </div>




        <?php endforeach; ?>


    <?php endif; ?>


</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>