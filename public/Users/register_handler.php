<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated path
require_once BASE_PATH . '/Models/Database.php'; // Ensure consistent path
require_once BASE_PATH . '/Controllers/UserController.php';

use MyApp\Models\Database;
use MyApp\Controllers\UserController;

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Create Database instance
    $pdo = $database->getConnection(); // Retrieve the PDO instance
    if ($pdo === null) {
        throw new Exception("Database connection failed. Please check your configuration.");
    }

    $userController = new UserController($pdo); // Pass the PDO instance to UserController

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userController->register($_POST); // Call the register method
        // Redirect to the login page after successful registration
        header("Location: " . BASE_URL . "Views/Users/login.php?success=" . urlencode("Registration successful. Please log in."));
        exit();
    }
} catch (Exception $e) {
    // Log the error and display a user-friendly message
    error_log("Error in register_handler.php: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/Users/register.php?error=" . urlencode("An error occurred during registration. Please try again."));
    exit();
}
?>
