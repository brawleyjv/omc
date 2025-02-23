<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Bom.php';
require_once __DIR__ . '/../../Controllers/BomController.php';

use MyApp\Controllers\BomController;
use MyApp\Models\Database;
use Globals\Config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
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
