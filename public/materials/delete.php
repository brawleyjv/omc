<?php
require_once '../../controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;

$controller = new MaterialController();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $controller->deleteMaterial($id);
} else {
    $id = $_GET['id'];
    $controller->viewMaterial($id);
    include '../../views/materials/delete.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Material</title>
    <link rel="stylesheet" href="../../public/css/styles.css"> <!-- Corrected the path to the CSS file in the root directory -->
</head>
<body>
    <?php include '../../views/header.php'; ?> <!-- Include the header file -->
    <h1>Delete Material</h1>
    <p>Are you sure you want to delete this material?</p>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($material['name']); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($material['description']); ?></p>
    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($material['quantity']); ?></p>
    <p><strong>Price:</strong> <?php echo htmlspecialchars($material['price']); ?></p>
    <form action="delete.php" method="post">
        <input type="hidden" name="id" value="<?php echo $material['id']; ?>">
        <input type="submit" value="Delete Material">
    </form>
    <a href="list.php" class="button">Back to List</a>
</body>
</html>
