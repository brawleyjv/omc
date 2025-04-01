<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Updated to use realpath
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$controller = new MaterialController($database); // Pass the required Database instance to the constructor

$material_name = isset($_GET['material_name']) ? $_GET['material_name'] : null;

if ($material_name) {
    $controller->deleteMaterialByName($material_name);
    echo "<script>alert('Material deleted successfully.'); window.location.href='" . BASE_URL . "public/materials/list_materials.php';</script>";
} else {
    echo "<script>alert('No material name provided.'); window.location.href='" . BASE_URL . "public/materials/list_materials.php';</script>";
}

$controller->closeConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use correct BASE_URL -->
</head>
<body>
</body>
</html>
