<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Updated path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $material_name = $_POST['material_name'] ?? '';
    $length = $_POST['length'] ?? '';
    $width = $_POST['width'] ?? '';
    $thickness = $_POST['thickness'] ?? '';
    $price = $_POST['price'] ?? '';
    $quantity_on_hand = $_POST['quantity_on_hand'] ?? '';
    $type = $_POST['type'] ?? '';
    $vendor = $_POST['vendor'] ?? '';
    $item_no = $_POST['item_no'] ?? '';
    $item_url = $_POST['item_url'] ?? '';
    $image_url = $_POST['image_url'] ?? '';

    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
    $controller = new MaterialController($database);

    $success = $controller->updateMaterialById(
        $id,
        $material_name,
        $length,
        $width,
        $thickness,
        $price,
        $quantity_on_hand,
        $type,
        $vendor,
        $item_no,
        $item_url,
        $image_url
    );

    if ($success) {
        echo "<script>alert('Material updated successfully.'); window.location.href='" . BASE_URL . "public/materials/list_materials.php';</script>";
    } else {
        echo "<script>alert('Failed to update material.'); window.history.back();</script>";
    }
}
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