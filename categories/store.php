<?php

require_once __DIR__ .
    '/../app/middleware/auth.php';

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
        BASE_URL . 'categories/'
    );

}


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$userId =
    (int) currentUserId();


if (!$userId) {

    setFlash(
        'danger',
        'Unable to identify the current user.'
    );

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$name =
    trim(
        $_POST['name'] ?? ''
    );


$description =
    trim(
        $_POST['description'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($name === '') {

    setFlash(
        'danger',
        'Please enter a category name.'
    );

    redirect(
        BASE_URL . 'categories/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Category Name Length
|--------------------------------------------------------------------------
*/

if (
    strlen($name) > 100
) {

    setFlash(
        'danger',
        'Category name must not exceed 100 characters.'
    );

    redirect(
        BASE_URL . 'categories/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Description Length
|--------------------------------------------------------------------------
*/

if (
    strlen($description) > 255
) {

    setFlash(
        'danger',
        'Description must not exceed 255 characters.'
    );

    redirect(
        BASE_URL . 'categories/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Check Existing User Category
|--------------------------------------------------------------------------
|
| A user should not be able to create the same
| expense category twice.
|
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id

    FROM categories

    WHERE user_id = :user_id

      AND name = :name

      AND type = 'Expense'

    LIMIT 1
");


$stmt->execute([

    ':user_id' =>
        $userId,

    ':name' =>
        $name

]);


if ($stmt->fetch()) {

    setFlash(
        'danger',
        'You already have an expense category with this name.'
    );

    redirect(
        BASE_URL . 'categories/create.php'
    );

}


/*
|--------------------------------------------------------------------------
| Insert Category
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO categories
    (
        user_id,
        name,
        type,
        description,
        status
    )

    VALUES
    (
        :user_id,
        :name,
        'Expense',
        :description,
        'Active'
    )
";


$stmt =
    $pdo->prepare($sql);


/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/

try {

    $stmt->execute([

        ':user_id' =>
            $userId,

        ':name' =>
            $name,

        ':description' =>
            $description

    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    setFlash(
        'success',
        'Expense category created successfully.'
    );


    redirect(
        BASE_URL . 'categories/'
    );


} catch (
    PDOException $e
) {

    /*
    |--------------------------------------------------------------------------
    | Database Error
    |--------------------------------------------------------------------------
    */

    setFlash(
        'danger',
        'Unable to create the expense category.'
    );


    redirect(
        BASE_URL . 'categories/create.php'
    );

}