
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

$password =
    $_POST['password'] ?? '';

$confirmPassword =
    $_POST['confirm_password']
    ?? '';

$role =
    $_POST['role'] ?? 'User';


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (
    $fullname === '' ||
    $username === '' ||
    $email === '' ||
    $password === ''
) {

    setFlash(
        'danger',
        'Please complete all required fields.'
    );

    redirect(
        BASE_URL . 'users/create.php'
    );

}


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
        BASE_URL . 'users/create.php'
    );

}


if (
    strlen($password) < 8
) {

    setFlash(
        'danger',
        'Password must contain at least 8 characters.'
    );

    redirect(
        BASE_URL . 'users/create.php'
    );

}


if (
    $password !== $confirmPassword
) {

    setFlash(
        'danger',
        'Passwords do not match.'
    );

    redirect(
        BASE_URL . 'users/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Role
|--------------------------------------------------------------------------
*/

$allowedRoles = [

    'Admin',

    'User'

];


if (
    !in_array(
        $role,
        $allowedRoles,
        true
    )
) {

    $role = 'User';

}


/*
|--------------------------------------------------------------------------
| Check Existing Username
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE username = :username
    LIMIT 1
");

$stmt->execute([

    ':username' =>
        $username

]);


if ($stmt->fetch()) {

    setFlash(
        'danger',
        'Username already exists.'
    );

    redirect(
        BASE_URL . 'users/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Check Existing Email
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE email = :email
    LIMIT 1
");

$stmt->execute([

    ':email' =>
        $email

]);


if ($stmt->fetch()) {

    setFlash(
        'danger',
        'Email address is already registered.'
    );

    redirect(
        BASE_URL . 'users/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Hash Password
|--------------------------------------------------------------------------
*/

$hashedPassword =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );


/*
|--------------------------------------------------------------------------
| Insert User
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO users
    (
        fullname,
        username,
        email,
        password,
        role,
        status
    )

    VALUES
    (
        :fullname,
        :username,
        :email,
        :password,
        :role,
        'Active'
    )
";


$stmt = $pdo->prepare($sql);


try {

    $stmt->execute([

        ':fullname' =>
            $fullname,

        ':username' =>
            $username,

        ':email' =>
            $email,

        ':password' =>
            $hashedPassword,

        ':role' =>
            $role

    ]);


    setFlash(
        'success',
        'User account created successfully.'
    );


    redirect(
        BASE_URL . 'users/'
    );


} catch (PDOException $e) {

    setFlash(
        'danger',
        'Unable to create the user account.'
    );


    redirect(
        BASE_URL . 'users/create.php'
    );

}

