<?php

/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Personal Finance System');
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', getenv('BASE_URL') ?: '/');
}


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

if (!defined('CURRENCY')) {
    define('CURRENCY', 'PHP');
}

// Use APP_CURRENCY_SYMBOL to avoid conflict with PHP's built-in POSIX CURRENCY_SYMBOL constant
if (!defined('APP_CURRENCY_SYMBOL')) {
    define('APP_CURRENCY_SYMBOL', '₱');
}

if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '₱');
}


/*
|--------------------------------------------------------------------------
| Environment & Error Reporting
|--------------------------------------------------------------------------
*/

if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}

if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}


/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    if (!headers_sent()) {
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
    }

    session_start();

}