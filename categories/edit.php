<?php

require_once __DIR__ .
    '/../app/middleware/auth.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$userId =
    (int) currentUserId();


if (!$userId) {

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Get Category ID
|--------------------------------------------------------------------------
*/

$categoryId =
    filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );


if (!$categoryId) {

    setFlash(
        'danger',
        'Invalid category.'
    );

    redirect(
        BASE_URL . 'categories/'
    );

}


/*
|--------------------------------------------------------------------------
| Get Category
|--------------------------------------------------------------------------
|
| Only allow the current user to edit their own
| Expense category.
|
| System categories have user_id = NULL and
| therefore cannot be edited.
|
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        user_id,
        name,
        type,
        description,
        status

    FROM categories

    WHERE id = :id

      AND user_id = :user_id

      AND type = 'Expense'

    LIMIT 1
";


$stmt =
    $pdo->prepare($sql);


$stmt->execute([

    ':id' =>
        $categoryId,

    ':user_id' =>
        $userId

]);


$category =
    $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Category Not Found
|--------------------------------------------------------------------------
*/

if (!$category) {

    setFlash(
        'danger',
        'Category not found or you do not have permission to edit it.'
    );

    redirect(
        BASE_URL . 'categories/'
    );

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
    Edit Category - <?= e(APP_NAME) ?>
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
    | Main Container
    |--------------------------------------------------------------------------
    */

    .category-container {

        width:
            100%;

        max-width:
            700px;

        margin:
            0 auto;

        padding:
            20px;

    }


    /*
    |--------------------------------------------------------------------------
    | Header
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
            24px;

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
    | Card
    |--------------------------------------------------------------------------
    */

    .category-card {

        background:
            #ffffff;

        border-radius:
            16px;

        padding:
            24px;

        box-shadow:
            0 4px 18px
            rgba(
                0,
                0,
                0,
                0.06
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    .form-label {

        font-weight:
            600;

        color:
            #334155;

    }


    .form-control {

        border-radius:
            10px;

        padding:
            11px 13px;

        border:
            1px solid
            #cbd5e1;

    }


    .form-control:focus {

        border-color:
            #198754;

        box-shadow:
            0 0 0
            0.2rem
            rgba(
                25,
                135,
                84,
                0.15
            );

    }


    .category-type {

        background:
            #fee2e2;

        color:
            #991b1b;

        border-radius:
            8px;

        padding:
            8px 12px;

        display:
            inline-block;

        font-size:
            13px;

        font-weight:
            700;

    }


    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .form-actions {

        display:
            flex;

        gap:
            10px;

        margin-top:
            24px;

    }


    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */

    @media (
        max-width: 575.98px
    ) {

        .category-container {

            padding:
                14px;

        }


        .category-card {

            padding:
                18px;

        }


        .page-title {

            font-size:
                21px;

        }


        .form-actions {

            flex-direction:
                column;

        }


        .form-actions .btn {

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

        <a
            href="<?= BASE_URL ?>categories/"
            class="text-white text-decoration-none"
        >

            <i
                class="
                    bi
                    bi-arrow-left
                    fs-4
                "
            ></i>

        </a>


        <span
            class="
                navbar-brand
                mb-0
                fw-bold
            "
        >

            💰 <?= e(APP_NAME) ?>

        </span>


        <span
            style="width: 24px;"
        ></span>

    </div>

</nav>


<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main
    class="
        category-container
    "
>


    <!-- Header -->

    <div
        class="
            page-header
        "
    >

        <a
            href="<?= BASE_URL ?>categories/"
            class="back-button"
            aria-label="Back to Categories"
        >

            <i
                class="
                    bi
                    bi-arrow-left
                "
            ></i>

        </a>


        <div
            class="
                page-title-wrapper
            "
        >

            <h1
                class="
                    page-title
                "
            >

                Edit Category

            </h1>


            <p
                class="
                    page-subtitle
                "
            >

                Update your expense category.

            </p>

        </div>

    </div>


    <!-- Category Form -->

    <div
        class="
            category-card
        "
    >

        <form
            action="<?= BASE_URL ?>categories/update.php"
            method="POST"
        >


            <!-- Category ID -->

            <input
                type="hidden"
                name="id"
                value="<?= (int) $category['id'] ?>"
            >


            <!-- Category Type -->

            <div
                class="
                    mb-4
                "
            >

                <label
                    class="
                        form-label
                    "
                >

                    Category Type

                </label>


                <div>

                    <span
                        class="
                            category-type
                        "
                    >

                        <i
                            class="
                                bi
                                bi-arrow-up-right
                                me-1
                            "
                        ></i>

                        Expense

                    </span>

                </div>

            </div>


            <!-- Category Name -->

            <div
                class="
                    mb-4
                "
            >

                <label
                    for="name"
                    class="
                        form-label
                    "
                >

                    Category Name

                    <span
                        class="
                            text-danger
                        "
                    >
                        *
                    </span>

                </label>


                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    maxlength="100"
                    value="<?= e(
                        $category['name']
                    ) ?>"
                    required
                    autofocus
                >

            </div>


            <!-- Description -->

            <div
                class="
                    mb-4
                "
            >

                <label
                    for="description"
                    class="
                        form-label
                    "
                >

                    Description

                </label>


                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="4"
                    maxlength="255"
                    placeholder="Describe what this category is used for..."
                ><?= e(
                    $category['description'] ?? ''
                ) ?></textarea>


                <div
                    class="
                        form-text
                    "
                >

                    Optional. Maximum 255 characters.

                </div>

            </div>


            <!-- Status -->

            <div
                class="
                    mb-4
                "
            >

                <label
                    class="
                        form-label
                    "
                >

                    Status

                </label>


                <div>

                    <?php if (
                        $category['status']
                        === 'Active'
                    ): ?>

                        <span
                            class="
                                badge
                                bg-success
                            "
                        >

                            Active

                        </span>

                    <?php else: ?>

                        <span
                            class="
                                badge
                                bg-secondary
                            "
                        >

                            Inactive

                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <!-- Actions -->

            <div
                class="
                    form-actions
                "
            >

                <a
                    href="<?= BASE_URL ?>categories/"
                    class="
                        btn
                        btn-outline-secondary
                    "
                >

                    <i
                        class="
                            bi
                            bi-x-circle
                            me-1
                        "
                    ></i>

                    Cancel

                </a>


                <button
                    type="submit"
                    class="
                        btn
                        btn-success
                    "
                >

                    <i
                        class="
                            bi
                            bi-check-circle
                            me-1
                        "
                    ></i>

                    Save Changes

                </button>

            </div>


        </form>

    </div>

</main>


</body>

</html>