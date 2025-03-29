<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define database credentials
define('DB_HOST', 'localhost'); // Replace with your database host
define('DB_USER', 'root');      // Replace with your database username
define('DB_PASSWORD', '');      // Replace with your database password
define('DB_NAME', 'omc_db');    // Replace with your database name

// Define absolute path to the application directory
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/'); // Dynamically set the absolute path
}

// Dynamically determine the base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // Normalize slashes

// Ensure BASE_URL points to the root of the application
//$baseDir = str_replace('/public', '', $scriptDir); // Adjust for public folder if necessary
//$baseDir = str_replace('/Views', '', $baseDir);   // Adjust for Views folder if necessary
define('BASE_URL', rtrim($protocol . $host . '/', '/') . '/'); // Ensure BASE_URL ends with a single slash
?>