<?php

namespace MyApp\Controllers;

require_once BASE_PATH . 'Models/CustomerModel.php'; // Include the CustomerModel class

use MyApp\Models\CustomerModel;

class CustomerController {
    private $customerModel;

    public function __construct($db) {
        $this->customerModel = new CustomerModel($db);
    }

    public function listCustomers() {
        return $this->customerModel->getAllCustomers();
    }

    public function viewCustomer($id) {
        return $this->customerModel->getCustomerById($id);
    }

    public function createCustomer($name, $email, $phone) {
        return $this->customerModel->addCustomer($name, $email, $phone);
    }

    public function editCustomer($id, $name, $email, $phone) {
        return $this->customerModel->updateCustomer($id, $name, $email, $phone);
    }

    public function removeCustomer($id) {
        return $this->customerModel->deleteCustomer($id);
    }
}
?>
