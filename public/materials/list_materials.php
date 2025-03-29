<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Material.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';
use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$materialsController = new MaterialController($database);
$materials = $materialsController->getAllMaterials();

include BASE_PATH . '/Views/materials/list_materials.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ...existing code... -->
    <link rel="stylesheet" href="/styles.css"> <!-- Updated path to root CSS file -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>