<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

use MyApp\Models\Database;
use MyApp\Controllers\VendorController;

// Initialize the database and controller
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure proper initialization
$vendorController = new VendorController($database); // Pass the database instance to the controller

$searchTerm = isset($_GET['search_term']) ? $_GET['search_term'] : '';

$vendors = [];
if (!empty($searchTerm)) {
    $vendors = $vendorController->searchVendorsByName($searchTerm); // Ensure this method exists in VendorController
}

include BASE_PATH . '/Views/vendors/search_results.php'; // Include the results view
?>
