<?php

/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

define('APP_NAME', 'Personal Finance System');

define('APP_VERSION', '1.0.0');

define(
    'BASE_URL',
    getenv('BASE_URL') ?: '/'
);


/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
*/

date_default_timezone_set('Asia/Manila');


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

define('CURRENCY', 'PHP');

define('CURRENCY_SYMBOL', '₱');


/*
|--------------------------------------------------------------------------
| Environment
|--------------------------------------------------------------------------
*/

define('APP_ENV', getenv('APP_ENV') ?: 'development');


/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();

}