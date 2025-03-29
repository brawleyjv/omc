<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Updated to use $_SERVER['DOCUMENT_ROOT']
require_once BASE_PATH . '/Views/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <div class="container">
        <h1>Customers</h1>
        <p>Manage your customers here.</p>
        <a href="<?php echo BASE_URL; ?>/views/main.php" class="btn styled-btn">Return to Main</a> <!-- Updated to use BASE_URL -->
    </div>
</body>
</html>
