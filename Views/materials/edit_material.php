<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$controller = new MaterialController($database);

$id = $_GET['id'] ?? null;

if ($id) {
    $material = $controller->getMaterialById($id); // Fetch material data by ID
    if ($material) {
        $material_name = $material['material_name'];
        $length = $material['Length'];
        $width = $material['Width'];
        $thickness = $material['Thickness'];
        $price = $material['Price'];
        $quantity_on_hand = $material['Quantity_on_Hand'];
        $type = $material['type'];
        $vendor = $material['vendor_name'];
        $item_no = $material['Item_no'];
        $item_url = $material['item_url'];
        $image_url = $material['image_url'];
    } else {
        echo "<script>alert('Material not found.'); window.location.href='" . BASE_URL . "public/materials/list_materials.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('No material ID provided.'); window.location.href='" . BASE_URL . "public/materials/list_materials.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $controller->updateMaterialByName(
        $_POST['material_name'],
        $_POST['length'],
        $_POST['width'],
        $_POST['thickness'],
        $_POST['price'],
        $_POST['quantity_on_hand'],
        $_POST['type'],
        $_POST['vendor'],
        $_POST['item_no'],
        $_POST['item_url'],
        $_POST['image_url']
    );

    if ($success) {
        echo "<script>alert('Material updated successfully.'); window.location.href='" . BASE_URL . "public/materials/list_materials.php';</script>";
    } else {
        echo "<script>alert('Failed to update material.');</script>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css">
    <style>
        select {
            padding: 10px;
            font-size: 16px;
            text-align: center;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-container {
            width: 100%;
            max-width: 600px;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 600px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <div class="container">
        <h1>Edit Material</h1>
        <form action="<?php echo BASE_URL; ?>public/materials/update_material.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <label for="material_name">Material Name:</label>
            <input type="text" name="material_name" value="<?php echo htmlspecialchars($material_name); ?>" required>
            <label for="length">Length:</label>
            <input type="number" step="0.01" name="length" value="<?php echo htmlspecialchars($length); ?>">
            <label for="width">Width:</label>
            <input type="number" step="0.01" name="width" value="<?php echo htmlspecialchars($width); ?>">
            <label for="thickness">Thickness:</label>
            <input type="number" step="0.01" name="thickness" value="<?php echo htmlspecialchars($thickness); ?>">
            <label for="price">Price:</label>
            <input type="text" name="price" value="<?php echo htmlspecialchars($price); ?>">
            <label for="quantity_on_hand">Quantity on Hand:</label>
            <input type="text" name="quantity_on_hand" value="<?php echo htmlspecialchars($quantity_on_hand); ?>">
            <label for="type">Type:</label>
            <input type="text" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <label for="vendor">Vendor:</label>
            <input type="text" name="vendor" value="<?php echo htmlspecialchars($vendor); ?>">
            <label for="item_no">Item No:</label>
            <input type="text" name="item_no" value="<?php echo htmlspecialchars($item_no); ?>">
            <label for="item_url">Item URL:</label>
            <input type="url" name="item_url" value="<?php echo htmlspecialchars($item_url); ?>">
            <label for="image_url">Image URL:</label>
            <input type="url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>">
            <div class="button-container">
                <button type="submit" class="btn styled-btn">Update Material</button>
                <button type="button" class="btn styled-btn red" onclick="window.location.href='<?php echo BASE_URL; ?>Views/materials/index.php'">Close</button>
            </div>
        </form>
    </div>
</body>
</html>

