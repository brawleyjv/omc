<?php
namespace MyApp\Models;

require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Include Database class

class VendorModel {
    private $connection;

    public function __construct($database) {
        if (!$database) {
            throw new \Exception('Database instance is not provided.');
        }
        $this->connection = $database->getConnection(); // Use the database connection
        if ($this->connection === null) {
            throw new \Exception('Database connection failed. Please check your database configuration.');
        }
    }

    public function getAllVendors() {
        $query = "SELECT id, vendor AS Vendor, phone, web_address, mailing_address, email_address FROM vendors";
        $stmt = $this->connection->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC); // Fetch all vendors as an associative array
    }

    public function getVendorById($vendorId) {
        $stmt = $this->connection->prepare('SELECT * FROM vendors WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
        return $stmt->fetch();
    }

    public function addVendor($vendorName, $vendorPhone = null, $vendorWebAddress = null, $vendorMailingAddress = null, $vendorEmailAddress = null) {
        if (!$this->connection) {
            throw new \Exception('Database connection is not established.');
        }
        $stmt = $this->connection->prepare('INSERT INTO vendors (Vendor, phone, web_address, mailing_address, email_address) VALUES (:Vendor, :phone, :web_address, :mailing_address, :email_address)');
        return $stmt->execute([
            'Vendor' => $vendorName,
            'phone' => $vendorPhone,
            'web_address' => $vendorWebAddress,
            'mailing_address' => $vendorMailingAddress,
            'email_address' => $vendorEmailAddress
        ]);
    }

    public function deleteVendor($vendorId) {
        if (!$this->connection) {
            throw new \Exception('Database connection is not established.');
        }
        $stmt = $this->connection->prepare('DELETE FROM vendors WHERE id = :id');
        return $stmt->execute(['id' => $vendorId]);
    }

    public function updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        if (!$this->connection) {
            throw new \Exception('Database connection is not established.');
        }
        $stmt = $this->connection->prepare('UPDATE vendors SET Vendor = :Vendor, phone = :phone, web_address = :web_address, mailing_address = :mailing_address, email_address = :email_address WHERE id = :id');
        return $stmt->execute([
            'id' => $vendorId,
            'Vendor' => $vendorName,
            'phone' => $vendorPhone,
            'web_address' => $vendorWebAddress,
            'mailing_address' => $vendorMailingAddress,
            'email_address' => $vendorEmailAddress
        ]);
    }

    public function searchVendorsByName($name) {
        if (!$this->connection) {
            throw new \Exception('Database connection is not established.');
        }
        $stmt = $this->connection->prepare('SELECT * FROM vendors WHERE Vendor LIKE :name');
        $stmt->execute(['name' => '%' . $name . '%']);
        return $stmt->fetchAll();
    }
}
?>
