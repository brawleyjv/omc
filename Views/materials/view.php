<?php include '../header.php'; ?> <!-- Corrected the path to the header file in the views directory -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Material</title>
    <link rel="stylesheet" href="../../public/css/styles.css"> <!-- Corrected the path to the CSS file in the root directory -->
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
    <a href="../../public/materials/list.php" class="button">Back to List</a>
</body>
</html>
