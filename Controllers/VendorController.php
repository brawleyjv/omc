<?php
include __DIR__ . '/../models/vendors.php';

class VendorController {
    public function addVendor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vendorName = $_POST['Vendor'];
            $vendorPhone = $_POST['phone'];
            $vendorWebAddress = $_POST['web_address'];
            $vendorMailingAddress = $_POST['mailing_address'];
            $vendorEmailAddress = $_POST['email_address'];
            
            $vendorModel = new VendorModel();
            $result = $vendorModel->addVendor($vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
            
            if ($result === 'exists') {
                echo 'exists';
            } elseif ($result) {
                echo 'Vendor added successfully';
            } else {
                echo 'Failed to add vendor';
            }
        } else {
            include '../Views/vendors/add_vendor.php';
        }
    }

    public function getVendors() {
        $vendorModel = new VendorModel();
        return $vendorModel->getAllVendors();
    }

    public function getVendorById($vendorId) {
        error_log("VendorController: getVendorById called with ID: $vendorId"); // Debugging: Log the method call
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->getVendorById($vendorId);
        error_log("VendorController: Vendor data retrieved: " . print_r($vendor, true)); // Debugging: Log the retrieved vendor data
        return $vendor;
    }

    public function deleteVendor($vendorId) {
        $vendorModel = new VendorModel();
        return $vendorModel->deleteVendor($vendorId);
    }

    public function updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        error_log("VendorController: updateVendor called with ID: $vendorId"); // Debugging: Log the method call
        $vendorModel = new VendorModel();
        $result = $vendorModel->updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
        error_log("VendorController: updateVendor result: " . ($result ? 'success' : 'failure')); // Debugging: Log the result of the update
        return $result;
    }
}
?>
