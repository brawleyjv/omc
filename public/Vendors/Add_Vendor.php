<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD); // Use DB_PASSWORD
$vendorController = new \MyApp\Controllers\VendorController($database);

// ...existing code...
?>
