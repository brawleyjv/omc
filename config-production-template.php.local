<?php
// Production config.php template
// Replace these values with your actual production credentials

// Start the session only if it is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DISABLE error reporting for production (security)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0); // Turn off error reporting for production

// Define constants only if they are not already defined
if (!defined('DB_HOST')) {
    define('DB_HOST', 'YOUR_PRODUCTION_DB_HOST'); // Replace with your hosting provider's DB host
}
if (!defined('DB_USER')) {
    define('DB_USER', 'YOUR_PRODUCTION_DB_USER'); // Replace with your production DB username
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', 'YOUR_PRODUCTION_DB_PASSWORD'); // Replace with your production DB password
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'YOUR_PRODUCTION_DB_NAME'); // Replace with your production DB name
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__) . '/');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://www.app.ozarkmadecrafts.com/'); // Your production URL
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

// Initialize a PDO instance for database connection
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed.");
}

// Remove debug logging for production security
// error_log("DB_HOST: " . DB_HOST);
// error_log("DB_NAME: " . DB_NAME);
// error_log("DB_USER: " . DB_USER);
// error_log("BASE_URL: " . BASE_URL);
?>
