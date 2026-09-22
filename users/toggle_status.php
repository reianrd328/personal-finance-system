<?php

require_once __DIR__ .
    '/../app/middleware/admin.php';

require_once __DIR__ .
    '/../app/config/database.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Get User ID
|--------------------------------------------------------------------------
*/

$userId =
    filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );


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
| Prevent Admin From Disabling Own Account
|--------------------------------------------------------------------------
*/

if (
    $userId ===
    (int) currentUserId()
) {

    setFlash(
        'danger',
        'You cannot deactivate your own account.'
    );

    redirect(
        BASE_URL . 'users/'
    );

}


/*
|--------------------------------------------------------------------------
| Get Current Status
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT status
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
| Toggle Status
|--------------------------------------------------------------------------
*/

$newStatus =
    $user['status'] === 'Active'
        ? 'Inactive'
        : 'Active';


$stmt = $pdo->prepare("
    UPDATE users

    SET status = :status

    WHERE id = :id
");


$stmt->execute([

    ':status' =>
        $newStatus,

    ':id' =>
        $userId

]);


setFlash(
    'success',
    'User status updated successfully.'
);


redirect(
    BASE_URL . 'users/'
);

