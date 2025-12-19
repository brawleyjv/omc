<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

$estimateId = $_GET['id'] ?? null;

if (!$estimateId) {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php?error=no_estimate_id");
    exit();
}

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    $estimateModel = new EstimateModel($conn);
    
    // Get estimate details
    $estimate = $estimateModel->getEstimateById($estimateId);
    
    if (!$estimate) {
        throw new Exception("Estimate not found");
    }
    
    if ($estimate['status'] !== 'approved') {
        throw new Exception("Only approved estimates can be converted to projects");
    }
    
    if ($estimate['project_id']) {
        throw new Exception("This estimate has already been converted to a project");
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Insert into projects table
    $projectQuery = "INSERT INTO projects (
        project_name, customer_name, project_description, 
        design_date, due_date, router_time, laser_time, labor_hours,
        file_upload, design_file, image_upload
    ) VALUES (
        :project_name, :customer_name, :project_description,
        :design_date, :due_date, :router_time, :laser_time, :labor_hours,
        '', '', ''
    )";
    
    $projectStmt = $conn->prepare($projectQuery);
    $projectStmt->execute([
        ':project_name' => $estimate['project_name'],
        ':customer_name' => $estimate['customer_name'],
        ':project_description' => $estimate['project_description'] ?? '',
        ':design_date' => date('Y-m-d'),
        ':due_date' => date('Y-m-d', strtotime('+30 days')),
        ':router_time' => $estimate['router_time'],
        ':laser_time' => $estimate['laser_time'],
        ':labor_hours' => $estimate['labor_hours']
    ]);
    
    $projectId = $conn->lastInsertId();
    
    // Create BOM entries from estimate materials
    if (!empty($estimate['materials'])) {
        $bomQuery = "INSERT INTO bom (
            project_id, project_name, material_name, material_type,
            length, width, thickness, quantity, material_id, materials
        ) VALUES (
            :project_id, :project_name, :material_name, 0,
            0, 0, 0, :quantity, 0, ''
        )";
        
        $bomStmt = $conn->prepare($bomQuery);
        
        foreach ($estimate['materials'] as $material) {
            $bomStmt->execute([
                ':project_id' => $projectId,
                ':project_name' => $estimate['project_name'],
                ':material_name' => $material['material_name'] . ' (' . $material['quantity'] . ' ' . $material['unit_type'] . ')',
                ':quantity' => $material['quantity']
            ]);
        }
    }
    
    // Update estimate with project_id and status
    $updateEstimate = "UPDATE estimates SET project_id = :project_id, status = 'converted' WHERE id = :estimate_id";
    $updateStmt = $conn->prepare($updateEstimate);
    $updateStmt->execute([
        ':project_id' => $projectId,
        ':estimate_id' => $estimateId
    ]);
    
    $conn->commit();
    
    // Redirect to the new project
    header("Location: " . BASE_URL . "Views/projects/view_project.php?id=" . $projectId . "&converted_from_estimate=1");
    exit();
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error converting estimate to project: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $estimateId . "&error=" . urlencode($e->getMessage()));
    exit();
}
?>