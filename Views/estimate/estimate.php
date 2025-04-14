<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated to use realpath(dirname(__FILE__))
require_once BASE_PATH . '/Views/header.php'; // Updated to use BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../../Views/header.php'); ?> <!-- Updated to use realpath -->
    <div class="container">
        <h1 class="title">Estimate</h1>
        <div class="menu">
            <button onclick="window.location.href='<?php echo BASE_URL; ?>Views/estimate/add_estimate.php';" class="btn styled-btn">Add Estimate</button> <!-- Updated to use BASE_URL -->
        </div>
    </div>
</body>
</html>
