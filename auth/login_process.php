<?php

require_once __DIR__ .
    '/../app/config/config.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$username = trim(
    $_POST['username'] ?? ''
);

$password =
    $_POST['password'] ?? '';


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
*/

if (
    $username === '' ||
    $password === ''
) {

    setFlash(
        'danger',
        'Please enter your username and password.'
    );

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Find User
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        fullname,
        username,
        email,
        password,
        role,
        status

    FROM users

    WHERE username = :username

    LIMIT 1
";


$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':username' => $username

]);


$user = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Verify User
|--------------------------------------------------------------------------
*/

if (
    !$user ||
    !password_verify(
        $password,
        $user['password']
    )
) {

    setFlash(
        'danger',
        'Invalid username or password.'
    );

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Check Account Status
|--------------------------------------------------------------------------
*/

if (
    $user['status'] !== 'Active'
) {

    setFlash(
        'danger',
        'Your account is currently inactive.'
    );

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Regenerate Session ID
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


/*
|--------------------------------------------------------------------------
| Store User Session
|--------------------------------------------------------------------------
*/

$_SESSION['user_id'] =
    $user['id'];

$_SESSION['user'] = [

    'id' =>
        $user['id'],

    'fullname' =>
        $user['fullname'],

    'username' =>
        $user['username'],

    'email' =>
        $user['email'],

    'role' =>
        $user['role']

];


/*
|--------------------------------------------------------------------------
| Login Successful
|--------------------------------------------------------------------------
*/

setFlash(
    'success',
    'Welcome back, ' .
    $user['fullname'] . '!'
);


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(
    BASE_URL . 'dashboard/'
);