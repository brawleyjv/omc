<?php
include __DIR__ . '/../Globals/config.php';

class VendorModel {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function addVendor($vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        // Check if vendor already exists (case-insensitive)
        $checkQuery = "SELECT * FROM vendors WHERE LOWER(vendor) = LOWER(?)";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("s", $vendorName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return 'exists';
        } else {
            // Insert vendor into the database
            $insertQuery = "INSERT INTO vendors (vendor, phone, web_address, mailing_address, email_address) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($insertQuery);
            $stmt->bind_param("sssss", $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);
            
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getAllVendors() {
        $query = "SELECT id, vendor AS Vendor, phone, web_address, mailing_address, email_address FROM vendors";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getVendorById($vendorId) {
        error_log("VendorModel: getVendorById called with ID: $vendorId"); // Debugging: Log the method call
        $query = "SELECT id, vendor AS Vendor, phone, web_address, mailing_address, email_address FROM vendors WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $vendorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $vendor = $result->fetch_assoc();
        error_log("VendorModel: Vendor data retrieved: " . print_r($vendor, true)); // Debugging: Log the retrieved vendor data
        return $vendor;
    }

    public function deleteVendor($vendorId) {
        $query = "DELETE FROM vendors WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $vendorId);
        return $stmt->execute();
    }

    public function updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        error_log("VendorModel: updateVendor called with ID: $vendorId"); // Debugging: Log the method call
        $query = "UPDATE vendors SET vendor = ?, phone = ?, web_address = ?, mailing_address = ?, email_address = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssi", $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress, $vendorId);
        $result = $stmt->execute();
        error_log("VendorModel: updateVendor result: " . ($result ? 'success' : 'failure')); // Debugging: Log the result of the update
        if (!$result) {
            error_log("VendorModel: updateVendor error: " . $stmt->error); // Debugging: Log the error if the update fails
        }
        return $result;
    }

    public function __destruct() {
        $this->conn->close();
    }
}
?>
