<?php
require_once realpath(dirname(__FILE__, 3) . '/config.php'); // Adjust path to config.php
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class
require_once BASE_PATH . 'Controllers/CustomerController.php'; // Include the CustomerController

use MyApp\Models\Database;
use MyApp\Controllers\CustomerController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    if (empty($id)) {
        header("Location: " . BASE_URL . "Views/customers/list_customers.php?error=Invalid customer ID");
        exit();
    }

    try {
        $db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $connection = $db->getConnection();
        $customerController = new CustomerController($connection);

        if ($customerController->removeCustomer($id)) {
            header("Location: " . BASE_URL . "Views/customers/list_customers.php?success=Customer deleted successfully");
        } else {
            header("Location: " . BASE_URL . "Views/customers/list_customers.php?error=Failed to delete customer");
        }
    } catch (Exception $e) {
        error_log("Error deleting customer: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/customers/list_customers.php?error=An error occurred while deleting the customer");
    }
    exit();
}
?>
