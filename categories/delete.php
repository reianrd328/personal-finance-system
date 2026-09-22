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
| Get Category ID
|--------------------------------------------------------------------------
*/

$categoryId =
    isset($_GET['id'])
        ? (int) $_GET['id']
        : 0;


if ($categoryId <= 0) {

    redirect(
        BASE_URL .
        'categories/'
    );

}


/*
|--------------------------------------------------------------------------
| Verify Category
|--------------------------------------------------------------------------
|
| Only the logged-in user's own custom
| categories can be deactivated.
|
*/

$sql = "

    SELECT
        id,
        name

    FROM categories

    WHERE id = :id

      AND user_id = :user_id

      AND type = 'Expense'

      AND status = 'Active'

    LIMIT 1

";


$stmt =
    $pdo->prepare($sql);


$stmt->execute([

    ':id' =>
        $categoryId,

    ':user_id' =>
        $userId

]);


$category =
    $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Category Not Found
|--------------------------------------------------------------------------
*/

if (!$category) {

    redirect(
        BASE_URL .
        'categories/'
    );

}


/*
|--------------------------------------------------------------------------
| Deactivate Category
|--------------------------------------------------------------------------
*/

$sql = "

    UPDATE categories

    SET
        status = 'Inactive',
        updated_at = NOW()

    WHERE id = :id

      AND user_id = :user_id

";


$stmt =
    $pdo->prepare($sql);


$stmt->execute([

    ':id' =>
        $categoryId,

    ':user_id' =>
        $userId

]);


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

redirect(
    BASE_URL .
    'categories/'
);