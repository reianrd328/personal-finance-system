<?php

require_once __DIR__ .
    '/../app/middleware/admin.php';

require_once __DIR__ .
    '/../app/config/theme.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';

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
        Add User -
        <?= e(APP_NAME) ?>
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >

</head>


<body>


<div
    class="container
    py-4"
>



<div class="mb-4">

    <a
        href="<?= BASE_URL ?>users/"
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

        Back to Users

    </a>


    <h2 class="fw-bold">

        Add User

    </h2>


    <p class="text-muted">

        Create another account for the system.

    </p>

</div>




    <div
        class="card
        border-0
        shadow-sm"
    >

        <div class="card-body p-4">


            <form
                action="create_process.php"
                method="POST"
            >


                <!-- Full Name -->

                <div class="mb-3">

                    <label
                        for="fullname"
                        class="form-label"
                    >

                        Full Name

                    </label>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        class="form-control"
                        required
                    >

                </div>


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
                        class="form-control"
                        required
                    >

                </div>


                <!-- Email -->

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label"
                    >

                        Email

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        required
                    >

                </div>


                <!-- Password -->

                <div class="mb-3">

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
                        class="form-control"
                        minlength="8"
                        required
                    >

                    <div
                        class="form-text"
                    >

                        Minimum 8 characters.

                    </div>

                </div>


                <!-- Confirm Password -->

                <div class="mb-3">

                    <label
                        for="confirm_password"
                        class="form-label"
                    >

                        Confirm Password

                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                        minlength="8"
                        required
                    >

                </div>


                <!-- Role -->

                <div class="mb-4">

                    <label
                        for="role"
                        class="form-label"
                    >

                        Role

                    </label>

                    <select
                        id="role"
                        name="role"
                        class="form-select"
                        required
                    >

                        <option
                            value="User"
                        >

                            User

                        </option>

                        <option
                            value="Admin"
                        >

                            Admin

                        </option>

                    </select>

                </div>


                <!-- Buttons -->

                <div
                    class="d-flex
                    flex-column
                    flex-sm-row
                    gap-2"
                >

                    <button
                        type="submit"
                        class="btn
                        btn-success"
                    >

                        Create User

                    </button>


                    <a
                        href="<?= BASE_URL ?>users/"
                        class="btn
                        btn-outline-secondary"
                    >

                        Cancel

                    </a>

                </div>


            </form>


        </div>

    </div>


</div>


</body>

</html>

