<?php
require_once realpath(dirname(__FILE__) . '/../config.php'); // Ensure the path is valid

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

// Adjust redirection logic to use BASE_URL defined in config.php
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/Views/Users/login.php"); // Use BASE_URL for redirection
    exit();
}

// Ensure this file only checks authentication without causing unnecessary redirects
?>
