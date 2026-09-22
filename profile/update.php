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
| Only POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        BASE_URL . 'profile/'
    );

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$fullname =
    trim(
        $_POST['fullname'] ?? ''
    );


$username =
    trim(
        $_POST['username'] ?? ''
    );


$email =
    trim(
        $_POST['email'] ?? ''
    );


$currentPassword =
    $_POST['current_password'] ?? '';


$newPassword =
    $_POST['new_password'] ?? '';


$confirmPassword =
    $_POST['confirm_password'] ?? '';


/*
|--------------------------------------------------------------------------
| Basic Validation
|--------------------------------------------------------------------------
*/

if (
    $fullname === '' ||
    $username === '' ||
    $email === ''
) {

    setFlash(
        'danger',
        'Please complete all required fields.'
    );

    redirect(
        BASE_URL . 'profile/edit.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Email
|--------------------------------------------------------------------------
*/

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    setFlash(
        'danger',
        'Please enter a valid email address.'
    );

    redirect(
        BASE_URL . 'profile/edit.php'
    );

}


/*
|--------------------------------------------------------------------------
| Check Username
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE username = :username
      AND id != :id
    LIMIT 1
");


$stmt->execute([

    ':username' =>
        $username,

    ':id' =>
        $userId

]);


if ($stmt->fetch()) {

    setFlash(
        'danger',
        'Username is already being used by another account.'
    );

    redirect(
        BASE_URL . 'profile/edit.php'
    );

}


/*
|--------------------------------------------------------------------------
| Check Email
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = :email
      AND id != :id
    LIMIT 1
");


$stmt->execute([

    ':email' =>
        $email,

    ':id' =>
        $userId

]);


if ($stmt->fetch()) {

    setFlash(
        'danger',
        'Email address is already registered to another account.'
    );

    redirect(
        BASE_URL . 'profile/edit.php'
    );

}


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

    ':id' =>
        $userId

]);


$user = $stmt->fetch();


if (!$user) {

    setFlash(
        'danger',
        'User account not found.'
    );

    redirect(
        BASE_URL . 'profile/'
    );

}


/*
|--------------------------------------------------------------------------
| Password Change
|--------------------------------------------------------------------------
*/

$passwordSql = '';

$params = [

    ':fullname' =>
        $fullname,

    ':username' =>
        $username,

    ':email' =>
        $email,

    ':id' =>
        $userId

];


if (
    $newPassword !== ''
    ||
    $currentPassword !== ''
    ||
    $confirmPassword !== ''
) {


    /*
    |--------------------------------------------------------------------------
    | Current Password Required
    |--------------------------------------------------------------------------
    */

    if ($currentPassword === '') {

        setFlash(
            'danger',
            'Please enter your current password.'
        );

        redirect(
            BASE_URL . 'profile/edit.php'
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
            BASE_URL . 'profile/edit.php'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | New Password Length
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
            BASE_URL . 'profile/edit.php'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Password
    |--------------------------------------------------------------------------
    */

    if (
        $newPassword !== $confirmPassword
    ) {

        setFlash(
            'danger',
            'New passwords do not match.'
        );

        redirect(
            BASE_URL . 'profile/edit.php'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Hash New Password
    |--------------------------------------------------------------------------
    */

    $passwordSql = ",
        password = :password
    ";


    $params[':password'] =
        password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

}


/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE users

    SET
        fullname = :fullname,
        username = :username,
        email = :email
        $passwordSql,
        updated_at = NOW()

    WHERE id = :id
";


try {

    $stmt =
        $pdo->prepare($sql);


    $stmt->execute(
        $params
    );


    /*
    |--------------------------------------------------------------------------
    | Refresh Session User
    |--------------------------------------------------------------------------
    */

    $_SESSION['user']['fullname'] =
        $fullname;

    $_SESSION['user']['username'] =
        $username;

    $_SESSION['user']['email'] =
        $email;


    setFlash(
        'success',
        'Your profile has been updated successfully.'
    );


    redirect(
        BASE_URL . 'profile/'
    );


} catch (PDOException $e) {

    setFlash(
        'danger',
        'Unable to update your profile.'
    );


    redirect(
        BASE_URL . 'profile/edit.php'
    );

}