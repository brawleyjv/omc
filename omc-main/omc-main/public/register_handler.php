<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Adjusted path for config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/UserController.php';

use Controllers\RegisterController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $user_type = $_POST['user_type'];
    $date_of_hire = $_POST['date_of_hire'];
    $password = $_POST['password'];

    // Validate input (basic example)
    if (empty($name) || empty($phone) || empty($position) || empty($user_type) || empty($date_of_hire) || empty($password)) {
        echo "<script>alert('All fields are required.'); window.location.href = '" . BASE_URL . "/Views/Users/register.php';</script>";
        exit();
    }

    // Use the RegisterController to handle registration
    $registerController = new RegisterController();
    if ($registerController->registerUser($name, $phone, $position, $user_type, $date_of_hire, $password)) {
        echo "<script>alert('User registered successfully!'); window.location.href = '" . BASE_URL . "/Views/login.php';</script>";
    } else {
        echo "<script>alert('Failed to register user. Please try again.'); window.location.href = '" . BASE_URL . "/Views/Users/register.php';</script>";
    }
}
?>