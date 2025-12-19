<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include config and models
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Establish database connection
        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn = $database->getPdo();
        $estimateModel = new EstimateModel($conn);

        // Prepare estimate data
        $estimateData = [
            'customer_name' => $_POST['customer_name'],
            'customer_email' => $_POST['customer_email'] ?? null,
            'customer_phone' => $_POST['customer_phone'] ?? null,
            'project_name' => $_POST['project_name'],
            'project_description' => $_POST['project_description'] ?? null,
            'router_time' => floatval($_POST['router_time'] ?? 0),
            'laser_time' => floatval($_POST['laser_time'] ?? 0),
            'labor_hours' => floatval($_POST['labor_hours'] ?? 0),
            'status' => $_POST['status'] ?? 'draft',
            'notes' => $_POST['notes'] ?? null,
            'created_by' => $_SESSION['user_id'] ?? null
        ];

        // Prepare materials data
        $materialsData = [];
        if (!empty($_POST['material_name'])) {
            foreach ($_POST['material_name'] as $index => $materialName) {
                if (!empty($materialName)) {
                    $quantity = floatval($_POST['material_quantity'][$index] ?? 0);
                    $unitCost = floatval($_POST['material_unit_cost'][$index] ?? 0);
                    
                    $materialsData[] = [
                        'material_name' => $materialName,
                        'quantity' => $quantity,
                        'unit_type' => $_POST['material_unit_type'][$index] ?? 'piece',
                        'unit_cost' => $unitCost,
                        'total_cost' => $quantity * $unitCost,
                        'notes' => null
                    ];
                }
            }
        }

        // Prepare custom items data
        $customItems = [];
        if (!empty($_POST['custom_item_name'])) {
            foreach ($_POST['custom_item_name'] as $index => $itemName) {
                if (!empty($itemName)) {
                    $customItems[] = [
                        'item_name' => $itemName,
                        'description' => null,
                        'cost' => floatval($_POST['custom_item_cost'][$index] ?? 0)
                    ];
                }
            }
        }

        // Create the estimate
        $result = $estimateModel->createEstimate($estimateData, $materialsData, $customItems);

        if ($result['success']) {
            // Redirect to estimate view page
            header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $result['estimate_id'] . "&success=1");
            exit();
        } else {
            throw new Exception("Failed to create estimate");
        }

    } catch (Exception $e) {
        error_log("Error saving estimate: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/estimate/create_new_estimate.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: " . BASE_URL . "Views/estimate/create_new_estimate.php");
    exit();
}
?>