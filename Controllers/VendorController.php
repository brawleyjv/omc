<?php
namespace MyApp\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/VendorModel.php';

class VendorController {
    public function addVendor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vendorName = $_POST['Vendor'];
            $vendorPhone = $_POST['phone'];
            $vendorWebAddress = $_POST['web_address'];
            $vendorMailingAddress = $_POST['mailing_address'];
            $vendorEmailAddress = $_POST['email_address'];
            
            $vendorModel = new \VendorModel(); // Ensure the correct namespace or global reference
            $result = $vendorModel->addVendor($vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
            
            if ($result === 'exists') {
                echo 'exists';
            } elseif ($result) {
                echo 'Vendor added successfully';
            } else {
                echo 'Failed to add vendor';
            }
        } else {
            include BASE_PATH . '/Views/vendors/add_vendor.php'; // Corrected path
        }
    }

    public function getVendors() {
        $vendorModel = new \VendorModel(); // Ensure the correct namespace or global reference
        return $vendorModel->getAllVendors();
    }

    public function getVendorById($vendorId) {
        error_log("VendorController: getVendorById called with ID: $vendorId"); // Debugging: Log the method call
        $vendorModel = new \VendorModel(); // Ensure the correct namespace or global reference
        $vendor = $vendorModel->getVendorById($vendorId);
        error_log("VendorController: Vendor data retrieved: " . print_r($vendor, true)); // Debugging: Log the retrieved vendor data
        return $vendor;
    }

    public function deleteVendor($vendorId) {
        $vendorModel = new \VendorModel(); // Ensure the correct namespace or global reference
        return $vendorModel->deleteVendor($vendorId);
    }

    public function updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        error_log("VendorController: updateVendor called with ID: $vendorId"); // Debugging: Log the method call
        $vendorModel = new \VendorModel(); // Ensure the correct namespace or global reference
        $result = $vendorModel->updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
        error_log("VendorController: updateVendor result: " . ($result ? 'success' : 'failure')); // Debugging: Log the result of the update
        return $result;
    }
}
?>
