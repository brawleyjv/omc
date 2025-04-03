<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

use MyApp\Models\Database;
use MyApp\Controllers\VendorController;

// Initialize the database
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization

// Initialize the VendorController with the database instance
$vendorController = new VendorController($database->getConnection());

// Retrieve all vendors
$vendors = $vendorController->getAllVendors(); // Use the new method to fetch all vendors

include BASE_PATH . '/Views/vendors/list_vendors.php'; // Include the view file
?>