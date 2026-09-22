<?php

require_once __DIR__ .
    '/../config/config.php';

require_once __DIR__ .
    '/../helpers/functions.php';


/*
|--------------------------------------------------------------------------
| Require Authentication
|--------------------------------------------------------------------------
*/

if (!isLoggedIn()) {

    redirect(
        BASE_URL . 'auth/login.php'
    );

}