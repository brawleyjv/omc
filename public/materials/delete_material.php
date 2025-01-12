<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$controller = new MaterialController($database);

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $controller->deleteMaterial($id);
    echo "<script>alert('Material deleted successfully.'); window.location.href='search_materials.php';</script>";
} else {
    echo "<script>alert('No material ID provided.'); window.location.href='search_materials.php';</script>";
}

$controller->closeConnection();
?>
