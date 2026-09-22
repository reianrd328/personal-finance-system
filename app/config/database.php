<?php

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
*/

$host     = getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port     = getenv('DB_PORT') ?: '4000';
$dbname   = getenv('DB_NAME') ?: 'personal_finance_db';
$username = getenv('DB_USER') ?: 'hHYKzueyzGLMEGS.root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '1m6DQcVGCCP5uwoD';
$charset  = 'utf8mb4';


/*
|--------------------------------------------------------------------------
| DSN
|--------------------------------------------------------------------------
*/

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";


/*
|--------------------------------------------------------------------------
| PDO Options
|--------------------------------------------------------------------------
*/

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// TiDB Cloud / SSL Configuration
$ssl = getenv('DB_SSL');
if ($ssl === 'true' || $ssl === '1' || (string)$port === '4000' || strpos($host, 'tidbcloud.com') !== false) {
    if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
        $caPath = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
        if (file_exists($caPath)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        }
    }
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}


/*
|--------------------------------------------------------------------------
| Create Connection
|--------------------------------------------------------------------------
*/

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    $env = getenv('APP_ENV') ?: (defined('APP_ENV') ? APP_ENV : 'development');
    if ($env === 'development') {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        error_log('Database connection failed: ' . $e->getMessage());
        die('Database connection failed. Please check your database settings or server configuration.');
    }
}