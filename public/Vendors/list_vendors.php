<?php
include '../../controllers/VendorController.php';

$vendorController = new VendorController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vendor_id'])) {
    $vendorId = $_POST['delete_vendor_id'];
    $vendorController->deleteVendor($vendorId);
    header('Location: list_vendors.php');
    exit;
}

$vendors = $vendorController->getVendors();

// Pass data to the view
include '../../Views/vendors/list_vendors.php';
?>