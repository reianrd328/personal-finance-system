<?php

require_once __DIR__ .
    '/../app/middleware/admin.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Only POST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD']
    !== 'POST'
) {

    redirect(
        BASE_URL . 'users/'
    );

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$userId =
    filter_input(
        INPUT_POST,
        'user_id',
        FILTER_VALIDATE_INT
    );

$newPassword =
    $_POST['new_password']
    ?? '';

$confirmPassword =
    $_POST['confirm_password']
    ?? '';


/*
|--------------------------------------------------------------------------
| Validate User ID
|--------------------------------------------------------------------------
*/

if (!$userId) {

    setFlash(
        'danger',
        'Invalid user.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


/*
|--------------------------------------------------------------------------
| Prevent Admin From Resetting Own Password Here
|--------------------------------------------------------------------------
*/

if (
    $userId ===
    (int) currentUserId()
) {

    setFlash(
        'danger',
        'You cannot reset your own password from User Management. Use your Profile password settings instead.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Password Fields
|--------------------------------------------------------------------------
*/

if (
    $newPassword === '' ||
    $confirmPassword === ''
) {

    setFlash(
        'danger',
        'Please complete both password fields.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


if (
    strlen($newPassword) < 8
) {

    setFlash(
        'danger',
        'New password must contain at least 8 characters.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


if (
    $newPassword !==
    $confirmPassword
) {

    setFlash(
        'danger',
        'New passwords do not match.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


/*
|--------------------------------------------------------------------------
| Check User Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, fullname
    FROM users
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([

    ':id' =>
        $userId

]);

$user = $stmt->fetch();


if (!$user) {

    setFlash(
        'danger',
        'User not found.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


/*
|--------------------------------------------------------------------------
| Hash New Password
|--------------------------------------------------------------------------
*/

$hashedPassword =
    password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );


/*
|--------------------------------------------------------------------------
| Update Password
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        UPDATE users

        SET
            password = :password,
            updated_at = NOW()

        WHERE id = :id
    ");

    $stmt->execute([

        ':password' =>
            $hashedPassword,

        ':id' =>
            $userId

    ]);


    setFlash(
        'success',
        'Password for ' . $user['fullname'] . ' has been reset successfully.'
    );


    redirect(
        BASE_URL . 'users/'
    );


} catch (PDOException $e) {

    setFlash(
        'danger',
        'Unable to reset the password. Please try again.'
    );


    redirect(
        BASE_URL . 'users/'
    );

}
