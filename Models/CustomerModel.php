<?php

namespace MyApp\Models;

use PDO;

class CustomerModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
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
