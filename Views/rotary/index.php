<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    require_once realpath(dirname(__FILE__) . '/../../config.php'); // Include config if not already included
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotary Management</title> <!-- Changed title back -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../header.php'); ?> <!-- Ensure header is included -->
    <div class="container">
        <h1 class="title">Rotary Management</h1> <!-- Changed title back -->
        <div style="text-align: center; margin-top: 20px;"> <!-- Center-align the button under the title -->
            <a href="<?php echo BASE_URL; ?>Views/rotary/rotary_setup.php" class="btn styled-btn">Rotary Setup</a>
        </div>
    </div>
</body>
</html>
