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
| Only POST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD']
    !== 'POST'
) {

    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$currentPassword =
    $_POST['current_password']
    ?? '';

$newPassword =
    $_POST['new_password']
    ?? '';

$confirmPassword =
    $_POST['confirm_password']
    ?? '';


/*
|--------------------------------------------------------------------------
| Validate Required Fields
|--------------------------------------------------------------------------
*/

if (
    $currentPassword === '' ||
    $newPassword === '' ||
    $confirmPassword === ''
) {

    setFlash(
        'danger',
        'Please complete all password fields.'
    );

    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}


/*
|--------------------------------------------------------------------------
| Get Current Password
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT password
    FROM users
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([

    ':id' =>
        $userId

]);


$user =
    $stmt->fetch();


if (!$user) {

    setFlash(
        'danger',
        'User account not found.'
    );

    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}


/*
|--------------------------------------------------------------------------
| Verify Current Password
|--------------------------------------------------------------------------
*/

if (
    !password_verify(
        $currentPassword,
        $user['password']
    )
) {

    setFlash(
        'danger',
        'Current password is incorrect.'
    );

    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}


/*
|--------------------------------------------------------------------------
| Password Length
|--------------------------------------------------------------------------
*/

if (
    strlen($newPassword) < 8
) {

    setFlash(
        'danger',
        'New password must contain at least 8 characters.'
    );

    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}


/*
|--------------------------------------------------------------------------
| Confirm Password
|--------------------------------------------------------------------------
*/

if (
    $newPassword !==
    $confirmPassword
) {

    setFlash(
        'danger',
        'New passwords do not match.'
    );

    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}


/*
|--------------------------------------------------------------------------
| Prevent Same Password
|--------------------------------------------------------------------------
*/

if (
    password_verify(
        $newPassword,
        $user['password']
    )
) {

    setFlash(
        'danger',
        'Your new password must be different from your current password.'
    );

    redirect(
        BASE_URL .
        'profile/change_password.php'
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


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    setFlash(
        'success',
        'Your password has been changed successfully.'
    );


    redirect(
        BASE_URL .
        'profile/'
    );


} catch (PDOException $e) {

    setFlash(
        'danger',
        'Unable to change your password. Please try again.'
    );


    redirect(
        BASE_URL .
        'profile/change_password.php'
    );

}