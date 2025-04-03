<?php

namespace MyApp\Controllers;

use PDO;
use MyApp\Models\CustomerModel; // Import the CustomerModel class
use MyApp\Models\ProjectModel; // Import the ProjectModel class

require_once realpath(dirname(__FILE__) . '/../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/CustomerModel.php'; // Ensure CustomerModel is included
require_once BASE_PATH . '/Models/ProjectModel.php'; // Ensure ProjectModel is included

class CustomerController {
    private PDO $database;
    private CustomerModel $customerModel;
    private ProjectModel $projectModel;

    public function __construct(PDO $database) {
        $this->database = $database;
        $this->customerModel = new CustomerModel($database);
        $this->projectModel = new ProjectModel($database);
    }

    public function listCustomers() {
        return $this->customerModel->getAllCustomers();
    }

    public function viewCustomer($id) {
        return $this->customerModel->getCustomerById($id);
    }

    public function createCustomer($name, $project, $address, $city, $state, $zip, $phone, $email, $notes) {
        return $this->customerModel->addCustomer($name, $project, $address, $city, $state, $zip, $phone, $email, $notes);
    }

    public function editCustomer($id, $name, $project, $address, $city, $state, $zip, $phone, $email, $notes) {
        return $this->customerModel->updateCustomer($id, $name, $project, $address, $city, $state, $zip, $phone, $email, $notes);
    }

    public function removeCustomer($id) {
        error_log("Debug: Calling CustomerModel->deleteCustomer with ID: $id");
        $result = $this->customerModel->deleteCustomer($id);
        if ($result) {
            error_log("Debug: CustomerModel->deleteCustomer returned true for ID: $id");
        } else {
            error_log("Debug: CustomerModel->deleteCustomer returned false for ID: $id");
        }
        return $result;
    }

    public function addCustomerWithProjects($name, $project, $address, $city, $state, $zip, $phone, $email, $notes, $projectIds) {
        $this->database->beginTransaction();
        try {
            // Add the customer
            $customerId = $this->customerModel->addCustomer($name, $project, $address, $city, $state, $zip, $phone, $email, $notes);

            // Assign projects to the customer directly in the `projects` table
            foreach ($projectIds as $projectId) {
                $query = "UPDATE projects SET customer_id = :customer_id WHERE project_id = :project_id";
                $stmt = $this->database->prepare($query);
                $stmt->bindValue(':customer_id', $customerId, PDO::PARAM_INT);
                $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->database->commit();
            return $customerId;
        } catch (\Exception $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    public function viewCustomerByName($name) {
        $query = "SELECT * FROM customers WHERE name = :name LIMIT 1";
        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
