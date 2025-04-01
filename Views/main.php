<?php
ob_start(); // Start output buffering to prevent premature output

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

require_once realpath(dirname(__FILE__) . '/../config.php');
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class

use MyApp\Models\Database; // Add this to use the Database class from the namespace

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dynamically resolve paths
require_once BASE_PATH . 'auth/check_auth.php'; // Corrected path to check_auth.php

// Log session details for debugging
error_log("Main.php: Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/header.php'); ?> <!-- Updated to use realpath -->
    <div class="container">
        <h1 class="title">Ozark Made Project Management System</h1>
        <h1 class="title">Main Menu</h1>
        <div class="button-container">
            <div class="button-row">
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="btn styled-btn">Projects</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="btn styled-btn">Materials</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="btn styled-btn">Customers</a> <!-- Corrected link to point to Views/customers/index.php -->
                <a href="<?php echo BASE_URL; ?>Views/equipment/index.php" class="btn styled-btn">Equipment</a>
                <a href="<?php echo BASE_URL; ?>Views/users/index.php" class="btn styled-btn">Users</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn styled-btn">Vendors</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/boardfeet.php" class="btn styled-btn">Board Feet</a>
                <a href="<?php echo BASE_URL; ?>Views/setup.php" class="btn styled-btn">Setup</a>
                <a href="<?php echo BASE_URL; ?>Views/Scale.php" class="btn styled-btn">Scale Project</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="btn styled-btn">Estimate</a>
                <a href="<?php echo BASE_URL; ?>Views/Chipload/chipload.php" class="btn styled-btn">Chipload</a>
                <a href="<?php echo BASE_URL; ?>Views/update.php" class="btn styled-btn">Update</a>
            </div>
        </div>
    </div>
</body>
</html>