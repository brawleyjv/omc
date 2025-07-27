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
    // Server Database Configuration (comment out when using local)
    // define('DB_HOST', 'db5017536213.hosting-data.io'); // Remote database host
    
    // Local Database Configuration (uncomment when using local)
    define('DB_HOST', 'localhost'); // Local database host
}
if (!defined('DB_USER')) {
    define('DB_USER', 'dbu2170183'); // Same username for both local and remote
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '#2025OzarkMade!'); // Same password for both local and remote
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'dbs14052036'); // Same database name for both local and remote
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__) . '/');
}
if (!defined('BASE_URL')) {
    if ($_SERVER['HTTP_HOST'] === 'localhost') {
        define('BASE_URL', 'http://localhost/omc/');
    } else {
        // Handle both www and non-www, but always use non-www in URLs
        define('BASE_URL', 'https://app.ozarkmadecrafts.com/');
    }
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
    
    // Create an alias for backward compatibility
    $conn = $db;
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