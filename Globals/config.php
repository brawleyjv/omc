<?php
namespace Globals;

class Config {
    const DB_HOST = 'localhost';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_NAME = 'omc_db';
    const BASE_URL = '/OMC/'; // Define the base URL for the project
}

// Define constants for database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', Config::DB_HOST);
}
if (!defined('DB_USER')) {
    define('DB_USER', Config::DB_USER);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', Config::DB_PASS);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', Config::DB_NAME);
}
if (!defined('BASE_URL')) {
    define('BASE_URL', Config::BASE_URL);
}

$servername = Config::DB_HOST;
$username = Config::DB_USER;
$password = Config::DB_PASS;
$dbname = Config::DB_NAME;

// Create connection
if (!extension_loaded('mysqli')) {
    die("The mysqli extension is not loaded.");
}

$conn = new \mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: Please check the server logs for more details.");
}
?>