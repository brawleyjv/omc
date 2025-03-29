<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Corrected path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate</title>
    <link rel="stylesheet" href="<?php echo 'http://localhost/omc/css/styles.css'; ?>"> <!-- Updated BASE_URL -->
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/header.php'; ?> <!-- Updated BASE_PATH -->
    <div class="container">
        <h1 class="title">Estimate</h1>
        <div class="menu">
            <button onclick="window.location.href='<?php echo 'http://localhost/omc/Views/estimate/add_estimate.php'; ?>'" class="btn styled-btn">Add Estimate</button> <!-- Updated BASE_URL -->
        </div>
    </div>
</body>
</html>
