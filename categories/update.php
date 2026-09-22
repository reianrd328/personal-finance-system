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
| Current User
|--------------------------------------------------------------------------
*/

$userId =
    (int) currentUserId();


if (!$userId) {

    redirect(
        BASE_URL . 'auth/login.php'
    );

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$categoryId =
    filter_input(
        INPUT_POST,
        'id',
        FILTER_VALIDATE_INT
    );


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
| Validate Category ID
|--------------------------------------------------------------------------
*/

if (!$categoryId) {

    setFlash(
        'danger',
        'Invalid category.'
    );

    redirect(
        BASE_URL . 'categories/'
    );

}


/*
|--------------------------------------------------------------------------
| Validate Category Name
|--------------------------------------------------------------------------
*/

if (
    $name === ''
) {

    setFlash(
        'danger',
        'Category name is required.'
    );

    redirect(
        BASE_URL .
        'categories/edit.php?id=' .
        $categoryId
    );

}


/*
|--------------------------------------------------------------------------
| Category Name Length
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
        BASE_URL .
        'categories/edit.php?id=' .
        $categoryId
    );

}


/*
|--------------------------------------------------------------------------
| Description Length
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
        BASE_URL .
        'categories/edit.php?id=' .
        $categoryId
    );

}


/*
|--------------------------------------------------------------------------
| Check Category Ownership
|--------------------------------------------------------------------------
|
| Only the logged-in user's own Expense category
| can be updated.
|
| System categories have:
|
| user_id = NULL
|
| Therefore they cannot be modified here.
|
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT
            id,
            name,
            description,
            status

        FROM categories

        WHERE id = :id

          AND user_id = :user_id

          AND type = 'Expense'

        LIMIT 1
    ");


$stmt->execute([

    ':id' =>
        $categoryId,

    ':user_id' =>
        $userId

]);


$category =
    $stmt->fetch();


if (!$category) {

    setFlash(
        'danger',
        'Category not found or you do not have permission to edit it.'
    );

    redirect(
        BASE_URL . 'categories/'
    );

}


/*
|--------------------------------------------------------------------------
| Check Duplicate Category Name
|--------------------------------------------------------------------------
|
| A user cannot have two Expense categories
| with the same name.
|
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT id

        FROM categories

        WHERE user_id = :user_id

          AND type = 'Expense'

          AND name = :name

          AND id != :id

        LIMIT 1
    ");


$stmt->execute([

    ':user_id' =>
        $userId,

    ':name' =>
        $name,

    ':id' =>
        $categoryId

]);


if ($stmt->fetch()) {

    setFlash(
        'danger',
        'You already have an Expense category with this name.'
    );

    redirect(
        BASE_URL .
        'categories/edit.php?id=' .
        $categoryId
    );

}


/*
|--------------------------------------------------------------------------
| Update Category
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE categories

    SET
        name = :name,
        description = :description,
        updated_at = CURRENT_TIMESTAMP

    WHERE id = :id

      AND user_id = :user_id

      AND type = 'Expense'
";


$stmt =
    $pdo->prepare($sql);


/*
|--------------------------------------------------------------------------
| Execute Update
|--------------------------------------------------------------------------
*/

try {

    $stmt->execute([

        ':name' =>
            $name,

        ':description' =>
            $description !== ''
                ? $description
                : null,

        ':id' =>
            $categoryId,

        ':user_id' =>
            $userId

    ]);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    setFlash(
        'success',
        'Expense category updated successfully.'
    );


    redirect(
        BASE_URL . 'categories/'
    );


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    */

    setFlash(
        'danger',
        'Unable to update the category.'
    );


    redirect(
        BASE_URL .
        'categories/edit.php?id=' .
        $categoryId
    );

}