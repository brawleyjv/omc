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

    public function getCustomerById($customerId) {
        $query = "SELECT * FROM customers WHERE customer_id = :customer_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addCustomer($name, $project, $address, $city, $state, $zip, $phone, $email, $notes, $project_ids) {
        $query = "INSERT INTO customers (name, Project, address, city, state, zip, phone, email, notes, project_ids) 
                  VALUES (:name, :project, :address, :city, :state, :zip, :phone, :email, :notes, :project_ids)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':project', $project, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':city', $city, PDO::PARAM_STR);
        $stmt->bindValue(':state', $state, PDO::PARAM_STR);
        $stmt->bindValue(':zip', $zip, PDO::PARAM_INT);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_INT);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindValue(':project_ids', $project_ids, PDO::PARAM_STR); // Store as JSON
        $stmt->execute();
        return $this->db->lastInsertId(); // Return the ID of the newly added customer
    }

    public function updateCustomer($customerId, $name, $project, $address, $city, $state, $zip, $phone, $email, $notes) {
        $query = "UPDATE customers SET 
                    name = :name, 
                    Project = :project, 
                    address = :address, 
                    city = :city, 
                    state = :state, 
                    zip = :zip, 
                    phone = :phone, 
                    email = :email, 
                    notes = :notes 
                  WHERE customer_id = :customer_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':project', $project, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':city', $city, PDO::PARAM_STR);
        $stmt->bindValue(':state', $state, PDO::PARAM_STR);
        $stmt->bindValue(':zip', $zip, PDO::PARAM_INT);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_INT);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteCustomer($id) {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE customer_id = ?");
        return $stmt->execute([$id]);
    }
}
