<?php
require_once __DIR__ . '/../../config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Controllers/VendorController.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors</title>
    <link rel="stylesheet" href="/styles.css"> <!-- Updated path to root CSS file -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <div class="container">
        <h1>Vendors</h1>
        <p>Manage your vendors here.</p>
        <a href="<?php echo BASE_URL; ?>views/main.php" class="btn styled-btn">Return to Main</a> <!-- Updated to use BASE_URL -->
    </div>
</body>
</html>
