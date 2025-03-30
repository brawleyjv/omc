<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Ensure proper inclusion of config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

// Adjust redirection logic to use BASE_URL defined in config.php
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/Views/Users/login.php");
    exit();
}

// Ensure this file only checks authentication without causing unnecessary redirects
?>
