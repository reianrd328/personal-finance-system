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
| Get Current User
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        fullname,
        username,
        email,
        role,
        status
    FROM users
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $userId
]);


$user = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| User Not Found
|--------------------------------------------------------------------------
*/

if (!$user) {

    setFlash(
        'danger',
        'User account not found.'
    );

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Flash Message
|--------------------------------------------------------------------------
*/

$flash = getFlash();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="theme-color"
    content="#198754"
>

<title>
    Change Password - <?= e(APP_NAME) ?>
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
    | Main Content
    |--------------------------------------------------------------------------
    */

    .profile-content {

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

    .profile-header {

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
            21px;

        box-shadow:
            0 2px 8px
            rgba(
                0,
                0,
                0,
                0.08
            );

    }


    .profile-title {

        margin:
            0;

        font-size:
            26px;

        font-weight:
            700;

    }


    .profile-subtitle {

        margin:
            4px 0 0;

        color:
            #64748b;

        font-size:
            14px;

    }


    /*
    |--------------------------------------------------------------------------
    | Password Card
    |--------------------------------------------------------------------------
    */

    .password-card {

        max-width:
            800px;

        border:
            0;

        border-radius:
            16px;

        background:
            #ffffff;

        box-shadow:
            0 4px 18px
            rgba(
                0,
                0,
                0,
                0.06
            );

    }


    .password-card-body {

        padding:
            28px;

    }


    /*
    |--------------------------------------------------------------------------
    | Section Header
    |--------------------------------------------------------------------------
    */

    .section-header {

        display:
            flex;

        align-items:
            center;

        gap:
            12px;

        margin-bottom:
            24px;

    }


    .section-icon {

        width:
            44px;

        height:
            44px;

        border-radius:
            12px;

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        background:
            #dbeafe;

        color:
            #2563eb;

        font-size:
            20px;

    }


    .section-title {

        margin:
            0;

        font-size:
            19px;

        font-weight:
            700;

    }


    .section-description {

        margin:
            3px 0 0;

        font-size:
            13px;

        color:
            #64748b;

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

        min-height:
            46px;

        border-radius:
            10px;

    }


    .form-control:focus {

        border-color:
            #198754;

        box-shadow:
            0 0 0 0.2rem
            rgba(
                25,
                135,
                84,
                0.15
            );

    }


    .form-text {

        color:
            #94a3b8;

    }


    /*
    |--------------------------------------------------------------------------
    | Password Requirements
    |--------------------------------------------------------------------------
    */

    .password-requirements {

        margin-top:
            20px;

        padding:
            16px;

        border-radius:
            10px;

        background:
            #f8fafc;

        border:
            1px solid
            #e2e8f0;

    }


    .password-requirements-title {

        font-size:
            13px;

        font-weight:
            700;

        margin-bottom:
            8px;

    }


    .password-requirements ul {

        margin:
            0;

        padding-left:
            20px;

        color:
            #64748b;

        font-size:
            13px;

    }


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    .form-actions {

        display:
            flex;

        justify-content:
            flex-end;

        gap:
            10px;

        margin-top:
            28px;

        padding-top:
            20px;

        border-top:
            1px solid
            #e2e8f0;

    }


    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */

    @media (
        max-width: 767.98px
    ) {

        .profile-content {

            margin-left:
                0;

            padding:
                16px;

        }


        .profile-title {

            font-size:
                22px;

        }


        .password-card-body {

            padding:
                20px;

        }


        .form-actions {

            flex-direction:
                column-reverse;

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
                    active
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
                strtolower(
                    $user['role'] ?? ''
                ) === 'admin'
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
                active
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
            strtolower(
                $user['role'] ?? ''
            ) === 'admin'
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
        profile-content
    "
>


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="profile-header">

        <a
            href="<?= BASE_URL ?>profile/"
            class="back-button"
            aria-label="Back to Profile"
        >

            ←

        </a>


        <div>

            <h1 class="profile-title">

                Change Password

            </h1>


            <p class="profile-subtitle">

                Update your account password securely.

            </p>

        </div>

    </div>


    <!-- =====================================================
         FLASH MESSAGE
    ====================================================== -->

    <?php if ($flash): ?>

        <div
            class="
                alert
                alert-<?= e(
                    $flash['type']
                )
                ?>
                alert-dismissible
                fade
                show
            "
            role="alert"
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


    <!-- =====================================================
         PASSWORD CARD
    ====================================================== -->

    <div class="password-card">

        <div class="password-card-body">


            <div class="section-header">

                <div class="section-icon">

                    <i
                        class="
                            bi
                            bi-shield-lock
                        "
                    ></i>

                </div>


                <div>

                    <h2 class="section-title">

                        Password Security

                    </h2>


                    <p class="section-description">

                        Choose a strong password that you do not use elsewhere.

                    </p>

                </div>

            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action="<?= BASE_URL ?>profile/change_password_action.php"
            >


                <!-- Current Password -->

                <div class="mb-3">

                    <label
                        for="current_password"
                        class="form-label"
                    >

                        Current Password

                    </label>


                    <input
                        type="password"
                        class="form-control"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >


                    <div class="form-text">

                        Enter your current password to verify your identity.

                    </div>

                </div>


                <!-- New Password -->

                <div class="mb-3">

                    <label
                        for="new_password"
                        class="form-label"
                    >

                        New Password

                    </label>


                    <input
                        type="password"
                        class="form-control"
                        id="new_password"
                        name="new_password"
                        autocomplete="new-password"
                        required
                    >


                    <div class="form-text">

                        Your new password must contain at least 8 characters.

                    </div>

                </div>


                <!-- Confirm Password -->

                <div class="mb-3">

                    <label
                        for="confirm_password"
                        class="form-label"
                    >

                        Confirm New Password

                    </label>


                    <input
                        type="password"
                        class="form-control"
                        id="confirm_password"
                        name="confirm_password"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <!-- =================================================
                     PASSWORD REQUIREMENTS
                ================================================== -->

                <div class="password-requirements">

                    <div class="password-requirements-title">

                        Password Requirements

                    </div>


                    <ul>

                        <li>
                            At least 8 characters
                        </li>

                        <li>
                            Use a combination of letters and numbers
                        </li>

                        <li>
                            Avoid using easily guessed information
                        </li>

                    </ul>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="form-actions">

                    <a
                        href="<?= BASE_URL ?>profile/"
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
                                bi-shield-check
                                me-1
                            "
                        ></i>

                        Change Password

                    </button>

                </div>


            </form>


        </div>

    </div>


</main>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>