<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated path
require_once BASE_PATH . '/Models/Database.php'; // Ensure consistent path
require_once BASE_PATH . '/Controllers/UserController.php';

use MyApp\Models\Database;
use MyApp\Controllers\UserController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$userController = new UserController($database);

// Hash all existing plain-text passwords
$userController->hashPasswordsForExistingUsers();

echo "All plain-text passwords have been hashed.";
?>
