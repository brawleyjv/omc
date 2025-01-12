<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Bom.php';

use MyApp\Models\Database;
use MyApp\Models\Bom;
use Globals\Config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
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

        $bom->addBom($project_id, $material_name, $material_type, $length, $width, $thickness, $quantity);
    }

    header("Location: estimate.php?project_id=$project_id");
    exit();
}
?>
