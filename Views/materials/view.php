<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Views/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Material</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
</head>
<body>
    <h1>View Material</h1>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($material['description']); ?></p>
    <p><strong>Length:</strong> <?php echo htmlspecialchars($material['length']); ?></p>
    <p><strong>Width:</strong> <?php echo htmlspecialchars($material['width']); ?></p>
    <p><strong>Thickness:</strong> <?php echo htmlspecialchars($material['thickness']); ?></p>
    <p><strong>Price:</strong> <?php echo htmlspecialchars($material['price']); ?></p>
    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($material['quantity']); ?></p>
    <p><strong>Type:</strong> <?php echo htmlspecialchars($material['type']); ?></p>
    <p><strong>Vendor:</strong> <?php echo htmlspecialchars($material['vendor']); ?></p>
    <p><strong>Item No:</strong> <?php echo htmlspecialchars($material['item_no']); ?></p>
    <p><strong>Item URL:</strong> <?php echo htmlspecialchars($material['item_url']); ?></p>
    <p><strong>Image URL:</strong> <?php echo htmlspecialchars($material['image_url']); ?></p>
    <a href="<?php echo BASE_URL; ?>public/materials/list.php" class="button">Back to List</a>
    <!-- Ensure BASE_URL points to localhost/omc -->
</body>
</html>
