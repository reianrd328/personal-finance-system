<?php

require_once __DIR__ .
    '/../app/config/config.php';

require_once __DIR__ .
    '/../app/helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Clear Session
|--------------------------------------------------------------------------
*/

$_SESSION = [];


/*
|--------------------------------------------------------------------------
| Destroy Session
|--------------------------------------------------------------------------
*/

if (
    ini_get('session.use_cookies')
) {

    $params =
        session_get_cookie_params();

    setcookie(

        session_name(),

        '',

        time() - 42000,

        $params['path'],

        $params['domain'],

        $params['secure'],

        $params['httponly']

    );

}


session_destroy();


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header(
    'Location: ' .
    BASE_URL .
    'auth/login.php'
);

exit;