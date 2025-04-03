<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/Bom.php';
require_once BASE_PATH . 'Controllers/BomController.php';

use MyApp\Controllers\BomController;
use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed
    $conn = $database->getConnection(); // Get the PDO connection
    $bomController = new BomController($conn); // Pass the PDO connection

    $id = $_POST['id']; // Use 'id'
    $project_name = $_POST['project_name']; // Retrieve project_name from the form
    $customer_name = $_POST['customer_name']; // Retrieve customer_name from the form
    $material_names = $_POST['material_name'];
    $lengths = $_POST['length'];
    $widths = $_POST['width'];
    $thicknesses = $_POST['thickness'];
    $quantities = $_POST['quantity'];

    $bomController->addBom($id, $material_names, $lengths, $widths, $thicknesses, $quantities);

    // Redirect back to add_estimate.php with id, project_name, and customer_name
    header("Location: " . BASE_URL . "Views/estimate/add_estimate.php?id=$id&project_name=" . urlencode($project_name) . "&customer_name=" . urlencode($customer_name));
    exit();
}
?>
