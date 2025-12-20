<?php

namespace MyApp\Models;

use PDO;

class CustomerModel {
    private $db;

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            // Create database connection if none provided
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function getAllCustomers() {
        $stmt = $this->db->query("SELECT * FROM customers ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerById($id) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchCustomers($query) {
        $searchTerm = "%" . $query . "%";
        $sql = "SELECT * FROM customers 
                WHERE name LIKE ? 
                   OR email LIKE ? 
                   OR phone LIKE ? 
                   OR city LIKE ? 
                   OR state LIKE ? 
                   OR address LIKE ?
                   OR zip LIKE ?
                   OR notes LIKE ?
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $searchTerm, $searchTerm, $searchTerm, $searchTerm, 
            $searchTerm, $searchTerm, $searchTerm, $searchTerm
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCustomer($name, $email, $phone) {
        $stmt = $this->db->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $phone]);
    }

    public function updateCustomer($id, $name, $email, $phone) {
        $stmt = $this->db->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $phone, $id]);
    }

    public function deleteCustomer($id) {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
