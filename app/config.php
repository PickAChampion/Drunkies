<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'drunkies_db');

// Application configuration
define('APP_NAME', 'Drunkies');
define('APP_URL', 'http://localhost/Drunkies-Application');
define('APP_ROOT', dirname(__DIR__));

// Path configurations
define('PUBLIC_PATH', APP_ROOT . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('PRODUCTS_PATH', UPLOADS_PATH . '/products');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('UTC'); 