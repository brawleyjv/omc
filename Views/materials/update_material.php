<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS); // Removed Globals\Config
$controller = new MaterialController($database);

$id = $_POST['id'];
$material_name = $_POST['material_name'];
$length = $_POST['length'];
$width = $_POST['width'];
$thickness = $_POST['thickness'];
$price = $_POST['price'];
$quantity_on_hand = $_POST['quantity_on_hand'];
$type = $_POST['type'];
$vendor = $_POST['vendor'];
$item_no = $_POST['item_no'];
$item_url = $_POST['item_url'];
$image_url = $_POST['image_url'];

$success = $controller->updateMaterial($id, $material_name, $length, $width, $thickness, $price, $quantity_on_hand, $type, $vendor, $item_no, $item_url, $image_url);

if ($success) {
    header('Location: ' . BASE_URL . 'public/materials/index.php');
    exit;
} else {
    echo "Failed to update material.";
}
?>