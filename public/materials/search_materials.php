<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Material.php';
require_once __DIR__ . '/../../Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$controller = new MaterialController($database);

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

if ($searchTerm) {
    $materials = $controller->searchMaterials($searchTerm);
} else {
    $materials = [];
}

include __DIR__ . '/../../Views/materials/search_materials.php';
?>

