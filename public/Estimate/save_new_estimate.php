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

        // If customer name is provided, check if they exist in customers table
        // If not, add them automatically
        $customerName = trim($_POST['customer_name'] ?? '');
        if (!empty($customerName)) {
            // Check if customer exists
            $checkCustomer = $conn->prepare("SELECT id FROM customers WHERE name = :name LIMIT 1");
            $checkCustomer->execute([':name' => $customerName]);
            $existingCustomer = $checkCustomer->fetch(PDO::FETCH_ASSOC);
            
            // If customer doesn't exist, create them
            if (!$existingCustomer) {
                $insertCustomer = $conn->prepare("
                    INSERT INTO customers (name, email, phone, address, city, state, zip, notes, Project) 
                    VALUES (:name, :email, :phone, :address, :city, :state, :zip, :notes, :project)
                ");
                $insertCustomer->execute([
                    ':name' => $customerName,
                    ':email' => $_POST['customer_email'] ?? null,
                    ':phone' => $_POST['customer_phone'] ?? null,
                    ':address' => $_POST['customer_address'] ?? null,
                    ':city' => $_POST['customer_city'] ?? null,
                    ':state' => $_POST['customer_state'] ?? null,
                    ':zip' => $_POST['customer_zip'] ?? null,
                    ':notes' => $_POST['customer_notes'] ?? null,
                    ':project' => $_POST['project_name'] ?? null
                ]);
            }
        }

        // Prepare estimate data
        $estimateData = [
            'customer_name' => $customerName,
            'customer_email' => $_POST['customer_email'] ?? null,
            'customer_phone' => $_POST['customer_phone'] ?? null,
            'project_name' => $_POST['project_name'],
            'project_description' => $_POST['project_description'] ?? null,
            'router_time' => floatval($_POST['router_time'] ?? 0),
            'laser_time' => floatval($_POST['laser_time'] ?? 0),
            'labor_hours' => floatval($_POST['labor_hours'] ?? 0),
            'bit_changes' => intval($_POST['bit_changes'] ?? 0),
            'needs_customization' => intval($_POST['needs_customization'] ?? 0),
            'shipping_cost' => floatval($_POST['shipping_cost'] ?? 0),
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
            $estimateId = $result['estimate_id'];
            
            // Auto-link to project ONLY if no customer or customer is "OMC"
            $customerName = trim($_POST['customer_name'] ?? '');
            $projectName = $_POST['project_name'] ?? '';
            
            // Link if customer is empty or "OMC" (base project, not custom order)
            if (!empty($projectName) && (empty($customerName) || strtoupper($customerName) === 'OMC')) {
                $checkProject = $conn->prepare("SELECT id FROM projects WHERE project_name = :project_name LIMIT 1");
                $checkProject->execute([':project_name' => $projectName]);
                $project = $checkProject->fetch(PDO::FETCH_ASSOC);
                
                if ($project) {
                    // Link estimate to project
                    $linkEstimate = $conn->prepare("
                        UPDATE estimates 
                        SET is_project_estimate = 1, project_id = :project_id 
                        WHERE id = :estimate_id
                    ");
                    $linkEstimate->execute([
                        ':project_id' => $project['id'],
                        ':estimate_id' => $estimateId
                    ]);
                    
                    // Link project to estimate (only if project doesn't already have one)
                    $linkProject = $conn->prepare("
                        UPDATE projects 
                        SET estimate_id = :estimate_id 
                        WHERE id = :project_id AND estimate_id IS NULL
                    ");
                    $linkProject->execute([
                        ':estimate_id' => $estimateId,
                        ':project_id' => $project['id']
                    ]);
                }
            }
            
            // Redirect to estimate view page
            header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $estimateId . "&success=1");
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