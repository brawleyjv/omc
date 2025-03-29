<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Use $_SERVER['DOCUMENT_ROOT'] for config.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session and redirect to the login page
session_destroy();
header("Location: " . BASE_URL . "Views/Users/login.php"); // Corrected path
exit();
