<?php
// Start the session only if it is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (only in development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define constants only if they are not already defined
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost'); // Replace with your database host
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root2'); // Replace with your database username
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '4873'); // Replace with your database password
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'dbs14052036'); // Replace with your database name
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__) . '/');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', ($_SERVER['HTTP_HOST'] === 'localhost') ? 'http://localhost/omc/' : 'https://www.app.ozarkmadecrafts.com/');
}
if (!defined('LOG_FILE')) {
    define('LOG_FILE', BASE_PATH . 'logs/error.log');
}

// Set include_path to the current directory to avoid conflicts
set_include_path(BASE_PATH);

// Ensure DB_PASS and DB_PASSWORD are interchangeable
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', DB_PASS);
}

// Initialize a PDO instance for database connection
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed.");
}

// Log defined constants for debugging
error_log("DB_HOST: " . DB_HOST);
error_log("DB_NAME: " . DB_NAME);
error_log("DB_USER: " . DB_USER);
error_log("BASE_URL: " . BASE_URL);
?>