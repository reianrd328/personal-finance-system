
<?php

require_once __DIR__ .
    '/auth.php';

require_once __DIR__ .
    '/../helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Check Administrator Role
|--------------------------------------------------------------------------
*/

$user = currentUser();


if (
    !$user ||
    $user['role'] !== 'Admin'
) {

    http_response_code(403);

    die(
        'Access denied. Administrator privileges are required.'
    );

}

