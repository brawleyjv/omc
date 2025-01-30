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

$dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME;
$username = Config::DB_USER;
$password = Config::DB_PASS;
$options = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
];

try {
    $connection = new \PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed: Please check the server logs for more details.");
}
?>