<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__) . '/../config.php');

// Redirect directly to the main page
header("Location: " . BASE_URL . "/Views/main.php"); // Adjusted path
exit();
?>
