<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Corrected path to Database.php
require_once BASE_PATH . '/Controllers/ProjectController.php';
require_once BASE_PATH . '/Controllers/CustomerController.php'; // Include CustomerController

use MyApp\Controllers\ProjectController;
use MyApp\Controllers\CustomerController;
use MyApp\Models\Database;

try {
    $database = new Database(); // Ensure the Database class is instantiated
    $db = $database->getConnection(); // Get the PDO connection
    if (!$db) {
        throw new Exception("Failed to initialize database connection.");
    }

    $projectController = new ProjectController($db); // Pass the PDO instance to the ProjectController
    $customerController = new CustomerController($db); // Pass the PDO instance to the CustomerController
} catch (Exception $e) {
    die("Error: " . $e->getMessage()); // Stop execution and display the error
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'] ?? '';
    $project_name = $_POST['project_name'] ?? '';
    $design_date = $_POST['design_date'] ?? '';
    $laser_time = $_POST['laser_time'] ?? 0;
    $router_time = $_POST['router_time'] ?? 0;
    $labor_hours = $_POST['labor_hours'] ?? 0;
    $project_description = $_POST['project_description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    $file_uploads = [];
    $image_uploads = [];
    $design_files = [];
    $upload_dir = BASE_PATH . 'projects/project_files/' . $project_name . '/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach (['file_upload', 'image_upload', 'design_file'] as $type) {
        if (!empty($_FILES[$type]['name'][0])) {
            foreach ($_FILES[$type]['name'] as $key => $name) {
                $tmp_name = $_FILES[$type]['tmp_name'][$key];
                $file_path = $upload_dir . $name;
                if (move_uploaded_file($tmp_name, $file_path)) {
                    ${$type . 's'}[] = basename($file_path);
                } else {
                    echo "Failed to upload $type: $name";
                    exit;
                }
            }
        }
    }

    try {
        // Fetch or create the customer and get the customer_id
        $customer = $customerController->viewCustomerByName($customer_name);
        if (!$customer) {
            // If customer doesn't exist, create it
            $customer_id = $customerController->createCustomer($customer_name, '', '', '', '', '', '', '', '');
        } else {
            $customer_id = $customer['customer_id'];
        }

        // Insert the customer_id into the `customer_project` table without a project_id
        $query = "INSERT IGNORE INTO customer_project (customer_id) VALUES (:customer_id)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();

        // Add the project to the `projects` table
        $project_id = $projectController->addProject(
            $project_name,
            $design_date,
            $customer_name,
            $laser_time,
            $router_time,
            $labor_hours,
            $project_description,
            $due_date,
            implode(',', $file_uploads),
            implode(',', $image_uploads),
            implode(',', $design_files),
            $customer_id
        );

        // Update the `customer_project` table with the project_id
        $query = "UPDATE customer_project SET project_id = :project_id WHERE customer_id = :customer_id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: ' . BASE_URL . 'Views/projects/view_project.php?project_name=' . urlencode($project_name));
        exit;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "<script>
                alert('A project with this name already exists. Please choose a different name.');
                window.location.href = '" . BASE_URL . "Views/projects/add_project.php';
            </script>";
        } else {
            echo 'Failed to add project: ', $e->getMessage();
        }
    }
} else {
    header('Location: ' . BASE_URL . 'Views/projects/add_project.php');
    exit();
}
?>