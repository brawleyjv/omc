<?php
ob_start(); // Start output buffering to prevent premature output

require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Explicitly reference the OMC directory

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
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <!-- ...existing code... -->
</body>
</html>