<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Updated path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$controller = new MaterialController($database);

$searchTerm = isset($_GET['search_term']) ? $_GET['search_term'] : '';

$results = [];
$noResults = false;

if (!empty($searchTerm)) {
    $results = $controller->searchMaterial($searchTerm);
    if (empty($results)) {
        $noResults = true;
    }
}

include BASE_PATH . '/Views/materials/search_results.php';
?>