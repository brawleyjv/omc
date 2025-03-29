<?php
ob_start(); // Start output buffering to prevent premature output

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Use $_SERVER['DOCUMENT_ROOT'] for config.php

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
require_once BASE_PATH . 'auth/check_auth.php'; // Use BASE_PATH for dynamic path resolution

// Log session details for debugging
error_log("Main.php: Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Include the header -->
    <div class="container">
        <h1 class="title">Ozark Made Project Management System</h1>
        <h1 class="title">Main Menu</h1>
        <div class="button-container">
            <div class="button-row">
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="btn styled-btn">Projects</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="btn styled-btn">Materials</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="btn styled-btn">Customers</a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/index.php" class="btn styled-btn">Equipment</a>
                <a href="<?php echo BASE_URL; ?>Views/users/index.php" class="btn styled-btn">Users</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn styled-btn">Vendors</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/boardfeet.php" class="btn styled-btn">Board Feet</a>
                <a href="<?php echo BASE_URL; ?>Views/setup.php" class="btn styled-btn">Setup</a>
                <a href="<?php echo BASE_URL; ?>Views/Scale.php" class="btn styled-btn">Scale Project</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="btn styled-btn">Estimate</a>
                <a href="<?php echo BASE_URL; ?>Views/Chipload/chipload.php" class="btn styled-btn">Chipload</a>
            </div>
        </div>
    </div>
</body>
</html>