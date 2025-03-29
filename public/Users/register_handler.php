<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Use $_SERVER['DOCUMENT_ROOT'] for config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/UserController.php';

use MyApp\Models\Database;
use MyApp\Controllers\UserController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Create Database instance
$userController = new UserController($database); // Pass Database instance to UserController

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->register($_POST);
}
?>
