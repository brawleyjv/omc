<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Explicitly reference the OMC directory
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Controllers/VendorController.php'; // Corrected path to VendorController.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1>Vendors</h1>
        <p>Manage your vendors here.</p>
        <a href="<?php echo BASE_URL; ?>views/main.php" class="btn styled-btn">Return to Main</a> <!-- Updated to use BASE_URL -->
    </div>
</body>
</html>
