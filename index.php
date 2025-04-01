<?php
  require_once realpath(dirname(__FILE__) . '/config.php'); 

if (!isset($_SESSION)) {
    session_start(); // Start session if not already started
}

// Log session details for debugging
error_log("Index.php: Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set"));

// Redirect to the main page if the user is already logged in and session is valid
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    error_log("Index.php: Redirecting to main.php");
    header("Location: " . BASE_URL . "Views/main.php");
    exit();
}

// Prevent redirect loop: Only redirect to login.php if not already on it
if (!str_contains($_SERVER['REQUEST_URI'], 'Views/Users/login.php')) {
    error_log("Index.php: Redirecting to login.php");
    header("Location: " . BASE_URL . "Views/Users/login.php");
    exit();
}

// If already on login.php, do nothing
error_log("Index.php: Already on login.php, no redirection.");
