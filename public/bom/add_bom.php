<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once realpath(dirname(__FILE__) . '/../../Models/Database.php');
require_once realpath(dirname(__FILE__) . '/../../Models/Bom.php');
require_once realpath(dirname(__FILE__) . '/../../Controllers/BomController.php');

use MyApp\Controllers\BomController;
use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed
    $bomController = new BomController($database);

    $project_name = $_POST['project_name'];
    $material_names = $_POST['material_name'];
    $lengths = $_POST['length'];
    $widths = $_POST['width'];
    $thicknesses = $_POST['thickness'];
    $quantities = $_POST['quantity'];

    $bomController->addBom($project_name, $material_names, $lengths, $widths, $thicknesses, $quantities);
}
?>
