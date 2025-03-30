<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Updated path
require_once BASE_PATH . '/Models/Database.php'; // Ensure consistent path
require_once BASE_PATH . '/Controllers/LoginController.php';

use MyApp\Models\Database;
use MyApp\Controllers\LoginController;

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Create Database instance
$loginController = new LoginController($database); // Pass Database instance to LoginController

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $loginController->login($username, $password); // Get user data on successful login

    if ($user) {
        $_SESSION['username'] = $user['name']; // Store username in session
        error_log("Login_handler.php: Session username set to: " . $_SESSION['username']);
        header("Location: " . BASE_URL . "Views/main.php"); // Redirect to main page
        exit();
    } else {
        error_log("Login_handler.php: Invalid login attempt for username: " . $username);
        header("Location: " . BASE_URL . "Views/Users/login.php?error=" . urlencode("Invalid username or password."));
        exit();
    }
}
?>
