<?php
require_once '../../controllers/MaterialController.php';
require_once '../../Globals/Config.php';

use MyApp\Controllers\MaterialController;
use Globals\Config;

$controller = new MaterialController();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    include '../../views/materials/create_form.php'; // Updated the include path
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material</title>
    <link rel="stylesheet" href="<?php echo Config::BASE_URL; ?>Public/css/styles.css"> <!-- Corrected the path to the CSS file -->
    <style>
        .title {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../../views/header.php'; ?> <!-- Include the header file -->
</body>
</html>
