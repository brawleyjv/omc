<?php
// filepath: /c:/xampp/htdocs/public/materials/add_material.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Updated path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Material.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;
use Globals\Config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
    $materialsController = new MaterialController($database);

    $data = [
        'material_name' => $_POST['material_name'],
        'length' => $_POST['length'] ?? null,
        'width' => $_POST['width'] ?? null,
        'thickness' => $_POST['thickness'] ?? null,
        'price' => $_POST['price'] ?? null,
        'quantity_on_hand' => $_POST['quantity_on_hand'] ?? null,
        'type' => $_POST['type'] ?? null,
        'vendor' => $_POST['vendor'] ?? null,
        'item_no' => $_POST['item_no'] ?? null,
        'item_url' => $_POST['item_url'] ?? null,
        'image_url' => $_POST['image_url'] ?? null
    ];

    $materialsController->submitMaterial(
        $data['material_name'],
        $data['length'],
        $data['width'],
        $data['thickness'],
        $data['price'],
        $data['quantity_on_hand'],
        $data['type'],
        $data['vendor'],
        $data['item_no'],
        $data['item_url'],
        $data['image_url']
    );

    header('Location: ' . BASE_URL . 'Views/materials/index.php');
    exit;
}

include __DIR__ . '/../../views/materials/add_material.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ...existing code... -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use correct BASE_URL -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>