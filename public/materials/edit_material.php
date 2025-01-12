<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        select {
            width: 80px;
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
    </style>
</head>
<body>
    <?php 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include '../../Views/header.php'; 
    ?>
    <div class="container">
        <div class="button-container">
            <form action="../../Views/materials/update_material.php" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                <input type="submit" value="Update Material" class="btn styled-btn">
            </form>
            <a href="javascript:history.back()" class="btn styled-btn">Cancel</a>
        </div>
        <h1>Edit Material</h1>
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        require_once __DIR__ . '/../../Globals/Config.php';
        require_once __DIR__ . '/../../Models/Database.php';
        require_once __DIR__ . '/../../Controllers/MaterialController.php';

        use MyApp\Controllers\MaterialController;
        use MyApp\Models\Database;
        use Globals\Config;

        $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
        $controller = new MaterialController($database);

        $id = isset($_GET['id']) ? $_GET['id'] : null;

        if ($id) {
            $material = $controller->getMaterialById($id);
            if ($material) {
                $types = $controller->getDistinctTypes();
                $vendors = $controller->getAllVendors();
            } else {
                echo "<p>Material not found.</p>";
                exit;
            }
        } else {
            echo "<p>No material ID provided.</p>";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = $controller->updateMaterial(
                $_POST['id'] ?? null,
                $_POST['material_name'] ?? null,
                $_POST['length'] ?? null,
                $_POST['width'] ?? null,
                $_POST['thickness'] ?? null,
                $_POST['price'] ?? null,
                $_POST['quantity_on_hand'] ?? null,
                $_POST['type'] ?? null,
                $_POST['vendor'] ?? null,
                $_POST['item_no'] ?? null,
                $_POST['item_url'] ?? null,
                $_POST['image_url'] ?? null
            );
            if ($success) {
                echo "<script>alert('Material updated successfully.'); window.location.href='../../Views/materials/index.php';</script>";
            } else {
                error_log("Failed to update material with ID: " . $_POST['id']);
                echo "<script>alert('Failed to update material.');</script>";
            }
            exit;
        }

        include __DIR__ . '/../../Views/materials/edit_material.php';
        ?>
        <?php if (isset($material) && $material): ?>
            <div class="form-container">
                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($material['id']); ?>">
                    <label for="material_name">Material Name:</label>
                    <input type="text" name="material_name" value="<?php echo htmlspecialchars($material['material_name']); ?>" required>
                    <label for="length">Length:</label>
                    <input type="number" step="0.01" name="length" value="<?php echo htmlspecialchars($material['Length']); ?>">
                    <label for="width">Width:</label>
                    <input type="number" step="0.01" name="width" value="<?php echo htmlspecialchars($material['Width']); ?>">
                    <label for="thickness">Thickness:</label>
                    <input type="number" step="0.01" name="thickness" value="<?php echo htmlspecialchars($material['Thickness']); ?>">
                    <label for="price">Price:</label>
                    <input type="text" name="price" value="<?php echo htmlspecialchars($material['Price']); ?>">
                    <label for="quantity_on_hand">Quantity on Hand:</label>
                    <input type="text" name="quantity_on_hand" value="<?php echo htmlspecialchars($material['Quantity_on_Hand']); ?>">
                    <label for="type">Type:</label>
                    <select name="type">
                        <?php foreach ($types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $type == $material['type'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="vendor">Vendor Name:</label>
                    <select name="vendor">
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?php echo htmlspecialchars($vendor['id']); ?>" <?php echo $vendor['id'] == $material['vendor'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vendor['vendor']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="item_no">Item No:</label>
                    <input type="text" name="item_no" value="<?php echo htmlspecialchars($material['Item_no']); ?>">
                    <label for="item_url">Item URL:</label>
                    <input type="text" name="item_url" value="<?php echo htmlspecialchars($material['item_url']); ?>">
                    <label for="image_url">Image URL:</label>
                    <input type="text" name="image_url" value="<?php echo htmlspecialchars($material['image_url']); ?>">
                    <input type="submit" value="Update Material" class="btn styled-btn">
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>