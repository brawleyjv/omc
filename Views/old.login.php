<?php
ob_start(); // Start output buffering to prevent premature output

require_once realpath(dirname(__FILE__) . '/../config.php'); // Updated to use realpath(dirname(__FILE__))
require_once BASE_PATH . '/Views/header.php'; // Updated to use BASE_PATH

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../Views/header.php'); ?> <!-- Updated to use realpath -->
    <!-- ...existing code... -->
</body>
</html>