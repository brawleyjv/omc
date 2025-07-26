<?php
namespace MyApp\Models;

require_once __DIR__ . '/../config.php';
require_once realpath(dirname(__FILE__) . '/../Models/Database.php');

class VendorModel {
    private $connection;

    public function __construct($database = null) {
        if ($database) {
            $this->connection = $database->getConnection();
        } else {
            // Create database connection if none provided
            try {
                $this->connection = new \PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
                $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                throw new \Exception('Database connection failed: ' . $e->getMessage());
            }
        }
        
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

    public function searchVendors($query) {
        if (!$this->connection) {
            throw new \Exception('Database connection is not established.');
        }
        $searchTerm = "%" . $query . "%";
        $sql = "SELECT id, vendor AS name, vendor AS company, phone, web_address, mailing_address, email_address AS email,
                       SUBSTRING_INDEX(mailing_address, ',', -2) AS state,
                       SUBSTRING_INDEX(SUBSTRING_INDEX(mailing_address, ',', -3), ',', 1) AS city
                FROM vendors 
                WHERE vendor LIKE ? 
                   OR phone LIKE ? 
                   OR email_address LIKE ? 
                   OR mailing_address LIKE ?
                   OR web_address LIKE ?
                ORDER BY vendor ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
