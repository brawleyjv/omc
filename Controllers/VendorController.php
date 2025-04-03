<?php
namespace MyApp\Controllers;

use PDO; // Import the PDO class
use Exception; // Import the Exception class
use PDOException; // Import the PDOException class
require_once realpath(dirname(__FILE__) . '/../config.php');
require_once realpath(dirname(__FILE__) . '/../Models/VendorModel.php');

use MyApp\Models\VendorModel; // Add the correct namespace for VendorModel

class VendorController {
    private $database; // Declare the $database property
    private $vendorModel;

    public function __construct(PDO $database) { // Ensure $database is of type PDO
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

    public function getVendors(): array {
        try {
            $query = "SELECT * FROM vendors"; // Replace 'vendors' with your actual table name
            $stmt = $this->database->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return the list of vendors as an array
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch vendors: " . $e->getMessage());
        }
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
            $query = "SELECT * FROM vendors";
            $stmt = $this->database->query($query); // Use the PDO object directly

            $vendors = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $vendors[] = $row;
            }

            return $vendors;
        } catch (PDOException $e) {
            error_log("VendorController: Error in listVendors(): " . $e->getMessage());
            throw new Exception("Error fetching vendors: " . $e->getMessage());
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
