<?php
require_once realpath(dirname(__FILE__, 3) . '/config.php'); // Adjust path to config.php
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class
require_once BASE_PATH . 'Controllers/CustomerController.php'; // Include the CustomerController

use MyApp\Models\Database;
use MyApp\Controllers\CustomerController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    error_log("Debug: Received customer ID: $id"); // Log the received ID

    if (empty($id)) {
        error_log("Debug: Invalid customer ID provided.");
        header("Location: " . BASE_URL . "Views/customers/list_customers.php?error=Invalid customer ID");
        exit();
    }

    try {
        $db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $connection = $db->getConnection();
        $customerController = new CustomerController($connection);

        error_log("Debug: Attempting to delete customer with ID: $id");

        if ($customerController->removeCustomer($id)) {
            error_log("Debug: Customer with ID $id deleted successfully.");
            header("Location: " . BASE_URL . "Views/customers/list_customers.php?success=Customer deleted successfully");
        } else {
            error_log("Debug: Failed to delete customer with ID $id.");
            header("Location: " . BASE_URL . "Views/customers/list_customers.php?error=Failed to delete customer");
        }
    } catch (Exception $e) {
        error_log("Error deleting customer: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/customers/list_customers.php?error=An error occurred while deleting the customer");
    }
    exit();
}
?>
