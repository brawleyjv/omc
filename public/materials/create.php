<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Updated path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Added to include the Database class
require_once BASE_PATH . '/Controllers/MaterialController.php'; // Include the MaterialController

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

// Pass the required argument to the MaterialController constructor
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$materialController = new MaterialController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'quantity' => $_POST['quantity'],
        'price' => $_POST['price']
    ];
    $result = $materialController->createMaterial($data);
    if ($result) {
        echo "<script>alert('Material added successfully.'); window.location.href = 'index.php';</script>";
    }
} else {
    include BASE_PATH . '/Views/materials/create_form.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use correct BASE_URL -->
    <style>
        .title {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
</body>
</html>
