<?php
namespace MyApp\Controllers;

require_once realpath(dirname(__FILE__) . '/../config.php');
require_once realpath(dirname(__FILE__) . '/../Models/VendorModel.php');

use MyApp\Models\VendorModel; // Add the correct namespace for VendorModel

class VendorController {
    private $database; // Declare the $database property
    private $vendorModel;

    public function __construct($database) {
        $this->database = $database; // Initialize the $database property
        $this->vendorModel = new VendorModel($this->database); // Pass the database instance to the VendorModel
    }

    public function addVendor() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vendorName = $_POST['Vendor'];
            $vendorPhone = $_POST['phone'] ?? null; // Allow null for optional fields
            $vendorWebAddress = $_POST['web_address'] ?? null; // Allow null for optional fields
            $vendorMailingAddress = $_POST['mailing_address'] ?? null; // Allow null for optional fields
            $vendorEmailAddress = $_POST['email_address'] ?? null; // Allow null for optional fields
            
            $result = $this->vendorModel->addVendor($vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
            
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
        return $this->vendorModel->getAllVendors(); // Use the initialized vendorModel
    }

    public function getVendorById($vendorId) {
        error_log("VendorController: getVendorById called with ID: $vendorId"); // Debugging: Log the method call
        $vendor = $this->vendorModel->getVendorById($vendorId); // Use the initialized vendorModel
        error_log("VendorController: Vendor data retrieved: " . print_r($vendor, true)); // Debugging: Log the retrieved vendor data
        return $vendor;
    }

    public function deleteVendor($vendorId) {
        return $this->vendorModel->deleteVendor($vendorId); // Use the initialized vendorModel
    }

    public function updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        error_log("VendorController: updateVendor called with ID: $vendorId"); // Debugging: Log the method call
        $result = $this->vendorModel->updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
        error_log("VendorController: updateVendor result: " . ($result ? 'success' : 'failure')); // Debugging: Log the result of the update
        return $result;
    }

    public function listVendors() {
        error_log("VendorController: listVendors() method called."); // Log method call
        try {
            $connection = $this->database->getConnection(); // Use the database object to get the connection

            $query = "SELECT * FROM vendors";
            $result = $connection->query($query);

            if (!$result) {
                throw new \Exception("Error fetching vendors: " . $connection->error);
            }

            $vendors = [];
            while ($row = $result->fetch_assoc()) {
                $vendors[] = $row;
            }

            $connection->close();
            return $vendors;
        } catch (\Exception $e) {
            error_log("VendorController: Error in listVendors(): " . $e->getMessage());
            throw $e;
        }
    }

    public function searchVendorsByName($name) {
        return $this->vendorModel->searchVendorsByName($name); // Call the model method
    }

    public function getAllVendors() {
        return $this->vendorModel->getAllVendors(); // Call the model's method to fetch all vendors
    }
}
?>
