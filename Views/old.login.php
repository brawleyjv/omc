<?php
ob_start(); // Start output buffering to prevent premature output

require_once BASE_PATH . '/config.php'; // Use BASE_PATH for dynamic path resolution

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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <!-- ...existing code... -->
</body>
</html>