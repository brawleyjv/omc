<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Material.php';

use MyApp\Models\Database;
use MyApp\Models\Material;
use Globals\Config;

$project_id = $_GET['project_id'];
$project_name = $_GET['project_name'];
$customer_name = $_GET['customer_name'];
$material_ids = isset($_GET['material_id']) ? $_GET['material_id'] : [];
$material_names = isset($_GET['material_name']) ? $_GET['material_name'] : [];
$lengths = isset($_GET['length']) ? $_GET['length'] : [];
$widths = isset($_GET['width']) ? $_GET['width'] : [];
$thicknesses = isset($_GET['thickness']) ? $_GET['thickness'] : [];
$quantities = isset($_GET['quantity']) ? $_GET['quantity'] : [];

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$materialModel = new Material($database);

$prices = [];
foreach ($material_ids as $material_id) {
    $prices[] = $materialModel->getPriceById($material_id);
}

// Pass variables to the HTML file
$customer_name = htmlspecialchars($customer_name);
$project_name = htmlspecialchars($project_name);

include __DIR__ . '/../../Views/estimate/estimate.php';
?>