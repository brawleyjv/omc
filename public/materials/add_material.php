<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Material.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $materialsController = new MaterialController($database);

        $data = [
            'material_name' => $_POST['material_name'] ?? '',
            'length' => !empty($_POST['length']) ? (float)$_POST['length'] : null,
            'width' => !empty($_POST['width']) ? (float)$_POST['width'] : null,
            'thickness' => !empty($_POST['thickness']) ? (float)$_POST['thickness'] : null,
            'price' => !empty($_POST['price']) ? (float)$_POST['price'] : null,
            'quantity_on_hand' => !empty($_POST['quantity_on_hand']) ? (int)$_POST['quantity_on_hand'] : null,
            'type' => $_POST['type'] ?? '',
            'vendor' => $_POST['vendor'] ?? '',
            'item_no' => $_POST['item_no'] ?? '',
            'item_url' => $_POST['item_url'] ?? '',
            'image_url' => $_POST['image_url'] ?? ''
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

        header('Location: ' . BASE_URL . 'Views/materials/list_materials.php?success=1');
        exit;
    } catch (Exception $e) {
        $error_message = "Error adding material: " . $e->getMessage();
        error_log($error_message);
        header('Location: ' . BASE_URL . 'Views/materials/add_material.php?error=' . urlencode($error_message));
        exit;
    }
}

// If not a POST request, include the form
$path = BASE_PATH . '/Views/materials/add_material.php';
if (file_exists($path)) {
    include $path;
} else {
    echo "<h1>Error</h1>";
    echo "<p>The add material form could not be found at: " . $path . "</p>";
    echo "<p><a href='" . BASE_URL . "Views/materials/list_materials.php'>Back to Materials</a></p>";
}
?>