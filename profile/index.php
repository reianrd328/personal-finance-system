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
    SELECT *
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
| User Information
|--------------------------------------------------------------------------
*/

$fullname =
    $user['fullname']
    ?? 'User';

$username =
    $user['username']
    ?? '—';

$email =
    $user['email']
    ?? '—';

$role =
    $user['role']
    ?? '—';

$status =
    $user['status']
    ?? '—';


/*
|--------------------------------------------------------------------------
| Role Badge
|--------------------------------------------------------------------------
*/

$roleClass =
    strtolower($role) === 'admin'
        ? 'role-admin'
        : 'role-user';


/*
|--------------------------------------------------------------------------
| Status Badge
|--------------------------------------------------------------------------
*/

$statusClass =
    strtolower($status) === 'active'
        ? 'status-active'
        : 'status-inactive';

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
    Profile - <?= e(APP_NAME) ?>
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
    href="<?= BASE_URL ?>assets/css/style_profile_index.css"
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
            href="<?= BASE_URL ?>dashboard/"
            class="back-button"
            aria-label="Back to Dashboard"
        >

            ←

        </a>


        <div>

            <h1 class="profile-title">
                My Profile
            </h1>

            <p class="profile-subtitle">
                View your account information.
            </p>

        </div>

    </div>


    <!-- =====================================================
         PROFILE CARD
    ====================================================== -->

    <div class="profile-card">


        <!-- Profile Banner -->

        <div class="profile-banner">

            <div class="profile-avatar">

                <i
                    class="
                        bi
                        bi-person
                    "
                ></i>

            </div>


            <div>

                <h2 class="profile-name">

                    <?= e($fullname) ?>

                </h2>


                <p class="profile-username">

                    @<?= e($username) ?>

                </p>

            </div>

        </div>


        <!-- Profile Information -->

        <div class="profile-body">

            <h3 class="section-title">

                Account Information

            </h3>


            <div class="profile-info">


                <!-- Full Name -->

                <div class="info-item">

                    <span class="info-label">
                        Full Name
                    </span>

                    <span class="info-value">

                        <?= e($fullname) ?>

                    </span>

                </div>


                <!-- Username -->

                <div class="info-item">

                    <span class="info-label">
                        Username
                    </span>

                    <span class="info-value">

                        <?= e($username) ?>

                    </span>

                </div>


                <!-- Email -->

                <div class="info-item">

                    <span class="info-label">
                        Email
                    </span>

                    <span class="info-value">

                        <?= e($email) ?>

                    </span>

                </div>


                <!-- Role -->

                <div class="info-item">

                    <span class="info-label">
                        Role
                    </span>

                    <span
                        class="
                            profile-badge
                            <?= e($roleClass) ?>
                        "
                    >

                        <?= e($role) ?>

                    </span>

                </div>


                <!-- Status -->

                <div class="info-item">

                    <span class="info-label">
                        Account Status
                    </span>

                    <span
                        class="
                            profile-badge
                            <?= e($statusClass) ?>
                        "
                    >

                        <?= e($status) ?>

                    </span>

                </div>

            </div>


            <!-- Actions -->

            <div class="profile-actions">

                <a
                    href="<?= BASE_URL ?>profile/edit.php"
                    class="btn btn-success"
                >
                    <i
                        class="
                            bi
                            bi-pencil
                            me-1
                        "
                    ></i>

                    Edit Profile
                </a>


                <a
                    href="<?= BASE_URL ?>profile/change_password.php"
                    class="btn btn-success"
                >
                    <i
                        class="
                            bi
                            bi-pencil
                            me-1
                        "
                    ></i>

                    Change Password
                </a>

            </div>

        </div>

    </div>


</main>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>