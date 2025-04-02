<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Ensure Database.php is included
require_once BASE_PATH . '/Models/User.php'; // Ensure User.php is included

use MyApp\Models\Database;
use MyApp\Models\User;

$database = new Database();
$db = $database->getConnection(); // Ensure $db is a PDO instance

if (!$db) { // Check if $db is null
    error_log("Database connection failed.");
    echo "<script>alert('Database connection failed. Please contact the administrator.'); window.location.href = '" . BASE_URL . "Views/Users/index.php';</script>";
    exit;
}

$userModel = new User($db); // Pass the PDO instance to the User model

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $position = $_POST['position'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $date_of_hire = $_POST['date_of_hire'] ?? '';
    $password = $_POST['password'] ?? null; // Password is optional

    if (empty($name) || empty($phone) || empty($position) || empty($user_type) || empty($date_of_hire)) {
        echo "<script>alert('All fields except password are required.'); window.location.href = '" . BASE_URL . "Views/Users/index.php';</script>";
        exit;
    }

    try {
        $updated = $userModel->updateUser($name, $phone, $position, $user_type, $date_of_hire, $password);

        if ($updated) {
            echo "<script>alert('Profile updated successfully.'); window.location.href = '" . BASE_URL . "Views/Users/index.php';</script>";
        } else {
            echo "<script>alert('Failed to update profile.'); window.location.href = '" . BASE_URL . "Views/Users/index.php';</script>";
        }
    } catch (Exception $e) {
        error_log("Error updating user profile: " . $e->getMessage());
        echo "<script>alert('An error occurred while updating the profile.'); window.location.href = '" . BASE_URL . "Views/Users/index.php';</script>";
    }
} else {
    header("Location: " . BASE_URL . "Views/Users/index.php");
    exit;
}
?>
