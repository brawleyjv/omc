<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php';
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php'; // Ensure the VendorController file is included

use MyApp\Models\Database;
use MyApp\Controllers\VendorController; // Import the correct namespace for VendorController

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$vendorController = new VendorController($database); // Instantiate VendorController with the database object

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vendor_id'])) {
    $vendorId = $_POST['delete_vendor_id'];
    $vendorController->deleteVendor($vendorId);
    header('Location: list_vendors.php');
    exit;
}

$vendors = $vendorController->listVendors(); // Call the method to list vendors

// Pass data to the view
include BASE_PATH . '/Views/vendors/list_vendors.php';
?>