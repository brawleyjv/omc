<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated path
require_once BASE_PATH . '/Models/Database.php'; // Ensure consistent path
require_once BASE_PATH . '/Controllers/UserController.php';

use MyApp\Models\Database;
use MyApp\Controllers\UserController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Create Database instance
$pdo = $database->getConnection(); // Retrieve the PDO instance
if ($pdo === null) {
    die("Database connection failed. Please check your configuration.");
}
$userController = new UserController($pdo); // Pass the PDO instance to UserController

// Hash all existing plain-text passwords
$userController->hashPasswordsForExistingUsers();

echo "All plain-text passwords have been hashed.";
?>
