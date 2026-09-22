<?php

require_once __DIR__ .
    '/../app/middleware/admin.php';

require_once __DIR__ .
    '/../app/config/theme.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Get Users
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        fullname,
        username,
        email,
        role,
        status,
        created_at

    FROM users

    ORDER BY created_at DESC
";

$stmt = $pdo->query($sql);

$users = $stmt->fetchAll();


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
        Users -
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


<body>


<div class="container-fluid p-3 p-md-4">



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


        <h2 class="fw-bold mb-1">

            User Management

        </h2>


        <p class="text-muted mb-0">

            Manage system users.

        </p>

    </div>


    <div
        class="d-flex
        align-items-start"
    >

        <a
            href="<?= BASE_URL ?>users/create.php"
            class="btn btn-success"
        >

            <i
                class="bi
                bi-person-plus
                me-1"
            ></i>

            Add User

        </a>

    </div>

</div>





    <!-- Flash -->

    <?php if ($flash): ?>

        <div
            class="alert
            alert-<?= e(
                $flash['type']
            ) ?>"
        >

            <?= e(
                $flash['message']
            ) ?>

        </div>

    <?php endif; ?>



    <!-- Users -->

    <div
        class="card
        border-0
        shadow-sm"
    >

        <div class="card-body">


            <div
                class="table-responsive"
            >

                <table
                    class="table
                    align-middle
                    mb-0"
                >

                    <thead>

                        <tr>

                            <th>Name</th>

                            <th>Username</th>

                            <th>Email</th>

                            <th>Role</th>

                            <th>Status</th>

                            <th>Created</th>

                            <th class="text-end">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        !$users
                    ): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center
                                text-muted
                                py-4"
                            >

                                No users found.

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach (
                        $users
                        as $account
                    ): ?>

                        <tr>


                            <td>

                                <strong>

                                    <?= e(
                                        $account[
                                            'fullname'
                                        ]
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= e(
                                    $account[
                                        'username'
                                    ]
                                ) ?>

                            </td>


                            <td>

                                <?= e(
                                    $account[
                                        'email'
                                    ]
                                ) ?>

                            </td>


                            <td>

                                <?php if (
                                    $account[
                                        'role'
                                    ] === 'Admin'
                                ): ?>

                                    <span
                                        class="badge
                                        bg-primary"
                                    >

                                        Admin

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="badge
                                        bg-secondary"
                                    >

                                        User

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php if (
                                    $account[
                                        'status'
                                    ] === 'Active'
                                ): ?>

                                    <span
                                        class="badge
                                        bg-success"
                                    >

                                        Active

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="badge
                                        bg-danger"
                                    >

                                        Inactive

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= e(
                                    date(
                                        'M d, Y',
                                        strtotime(
                                            $account[
                                                'created_at'
                                            ]
                                        )
                                    )
                                ) ?>

                            </td>


                            <td
                                class="text-end"
                            >

                                <?php if (
                                    $account[
                                        'id'
                                    ]
                                    !=
                                    currentUserId()
                                ): ?>

                                    <div class="d-flex justify-content-end gap-2">

                                        <a
                                            href="<?= BASE_URL ?>users/toggle_status.php?id=<?= (int) $account['id'] ?>"
                                            class="btn
                                            btn-sm
                                            btn-outline-secondary"
                                        >

                                            <?php if (
                                                $account[
                                                    'status'
                                                ]
                                                ===
                                                'Active'
                                            ): ?>

                                                Deactivate

                                            <?php else: ?>

                                                Activate

                                            <?php endif; ?>

                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#resetPasswordModal"
                                            data-user-id="<?= (int) $account['id'] ?>"
                                            data-user-name="<?= e($account['fullname']) ?>"
                                        >

                                            <i class="bi bi-key me-1"></i>
                                            Reset Password

                                        </button>

                                    </div>

                                <?php else: ?>

                                    <span
                                        class="text-muted
                                        small"
                                    >

                                        Current account

                                    </span>

                                <?php endif; ?>

                            </td>


                        </tr>

                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


</div>


<!-- Reset Password Modal -->

<div
    class="modal fade"
    id="resetPasswordModal"
    tabindex="-1"
    aria-labelledby="resetPasswordModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="<?= BASE_URL ?>users/reset_password.php"
                autocomplete="off"
            >

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="resetPasswordModalLabel"
                    >

                        <i class="bi bi-key me-2"></i>
                        Reset User Password

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="user_id"
                        id="resetUserId"
                    >

                    <p class="mb-3">
                        You are resetting the password for
                        <strong id="resetUserName"></strong>.
                    </p>

                    <div class="mb-3">

                        <label
                            for="newPassword"
                            class="form-label"
                        >
                            New Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="newPassword"
                            name="new_password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >

                        <div class="form-text">
                            Password must contain at least 8 characters.
                        </div>

                    </div>

                    <div class="mb-2">

                        <label
                            for="confirmPassword"
                            class="form-label"
                        >
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="confirmPassword"
                            name="confirm_password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        <i class="bi bi-key me-1"></i>
                        Reset Password
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
></script>

<script>

const resetPasswordModal = document.getElementById('resetPasswordModal');

resetPasswordModal.addEventListener('show.bs.modal', function (event) {

    const button = event.relatedTarget;
    const userId = button.getAttribute('data-user-id');
    const userName = button.getAttribute('data-user-name');

    document.getElementById('resetUserId').value = userId;
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';

});

</script>


</body>

</html>
