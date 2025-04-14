<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Updated to use realpath
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Models\Database;
use MyApp\Controllers\MaterialController;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD); // Ensure Database is instantiated with required arguments
$controller = new MaterialController($database); // Pass the $database object to the constructor

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'quantity' => $_POST['quantity'],
        'price' => $_POST['price']
    ];
    $result = $controller->createMaterial($data);
    if ($result) {
        echo "<script>alert('Material added successfully.'); window.location.href = 'index.php';</script>";
    }
} else {
    include realpath(dirname(__FILE__) . '/../../Views/materials/create_form.php'); // Updated to use realpath
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
