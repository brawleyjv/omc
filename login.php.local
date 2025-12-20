<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Flexible path to config.php that works with different directory structures
$config_path = __DIR__ . '/config.php';
if (!file_exists($config_path)) {
    // Try alternative paths for different server structures
    $config_path = __DIR__ . '/../config.php';
}
if (!file_exists($config_path)) {
    // Search up the directory tree
    $current_dir = __DIR__;
    for ($i = 0; $i < 3; $i++) {
        $test_path = $current_dir . '/config.php';
        if (file_exists($test_path)) {
            $config_path = $test_path;
            break;
        }
        $current_dir = dirname($current_dir);
    }
}
require_once $config_path;

// Ensure $conn is available for backward compatibility
if (!isset($conn) && isset($db)) {
    $conn = $db;
}

// Check if this is a POST request (login attempt)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirect to the login handler
    header('Location: ' . BASE_URL . 'public/Users/login_handler.php');
    exit();
}

// Otherwise redirect to the proper login page
header('Location: ' . BASE_URL . 'Views/Users/login.php');
exit();
?>
