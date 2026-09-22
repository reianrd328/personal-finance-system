<?php

require_once __DIR__ .
    '/../app/config/config.php';

require_once __DIR__ .
    '/../app/config/theme.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| If already logged in
|--------------------------------------------------------------------------
*/

if (isLoggedIn()) {

    redirect(
        BASE_URL . 'dashboard/'
    );

}


$flash = getFlash();

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
        Login - <?= e(APP_NAME) ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/style.css"
    >

</head>


<body>


<div class="container-fluid min-vh-100">

    <div
        class="row
        min-vh-100
        justify-content-center
        align-items-center"
    >

        <div
            class="col-12
            col-sm-10
            col-md-6
            col-lg-4"
        >

            <div
                class="card
                border-0
                shadow-sm"
            >

                <div class="card-body p-4">


                    <!-- Logo / Title -->

                    <div
                        class="text-center
                        mb-4"
                    >

                        <div
                            class="mb-3"
                            style="font-size: 48px;"
                        >
                            💰
                        </div>

                        <h3
                            class="fw-bold"
                        >
                            <?= e(APP_NAME) ?>
                        </h3>

                        <p
                            class="text-muted
                            mb-0"
                        >
                            Manage your money
                            with ease.
                        </p>

                    </div>


                    <!-- Flash Message -->

                    <?php if ($flash): ?>

                        <div
                            class="alert
                            alert-<?= e($flash['type']) ?>"
                        >

                            <?= e(
                                $flash['message']
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <!-- Login Form -->

                    <form
                        action="login_process.php"
                        method="POST"
                    >


                        <!-- Username -->

                        <div class="mb-3">

                            <label
                                for="username"
                                class="form-label"
                            >
                                Username
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control
                                form-control-lg"
                                placeholder="Enter username"
                                autocomplete="username"
                                required
                            >

                        </div>


                        <!-- Password -->

                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control
                                form-control-lg"
                                placeholder="Enter password"
                                autocomplete="current-password"
                                required
                            >

                        </div>


                        <!-- Login -->

                        <button
                            type="submit"
                            class="btn btn-success
                            btn-lg w-100"
                        >

                            Login

                        </button>


                    </form>


                    <div
                        class="text-center
                        mt-4"
                    >

                        <small
                            class="text-muted"
                        >

                            <?= e(APP_NAME) ?>

                            v<?= e(APP_VERSION) ?>

                        </small>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


</body>

</html>