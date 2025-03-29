<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Updated path to config.php
require_once BASE_PATH . '/Views/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials Index</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use correct BASE_URL -->
</head>
<body>
    <h1>Welcome to Materials Management</h1>
    <a href="<?php echo BASE_URL; ?>public/materials/list_materials.php">View Materials</a>
    <a href="<?php echo BASE_URL; ?>public/materials/add_material.php">Add Material</a>
</body>
</html>
