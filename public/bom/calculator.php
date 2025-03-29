<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Ensure Config is included
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/Models/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/Models/Bom.php';

use MyApp\Models\Database;
use MyApp\Models\Bom;
// Remove the namespace import for Config if it is not in the MyApp namespace
// use MyApp\Config;

if (!class_exists('Config')) {
    die('Config class not found. Please check the config.php file.');
}

if (!defined('BASE_URL')) {
    die('BASE_URL constant not defined. Please check the config.php file.');
}

if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
    die('Database constants are not defined. Please check the config.php file.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated to use constants directly
    $bom = new Bom($database);

    $project_id = $_POST['project_id'];
    $material_names = $_POST['material_name'];
    $material_types = $_POST['material_type'];
    $lengths = $_POST['length'];
    $widths = $_POST['width'];
    $thicknesses = $_POST['thickness'];
    $quantities = $_POST['quantity'];

    foreach ($material_names as $index => $material_name) {
        $material_type = $material_types[$index];
        $length = $lengths[$index];
        $width = $widths[$index];
        $thickness = $thicknesses[$index];
        $quantity = $quantities[$index];

        $bom->addBom($material_name, $material_type, $length, $width, $thickness, $quantity);
    }

    header("Location: " . BASE_URL . "public/bom/estimate.php?project_id=$project_id");
    exit();
}
?>
