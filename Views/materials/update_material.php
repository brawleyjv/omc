<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
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
    header('Location: ../../public/materials/index.php');
    exit;
} else {
    echo "Failed to update material.";
}
?>