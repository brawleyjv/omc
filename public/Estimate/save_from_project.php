<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/EstimateModel.php';
require_once BASE_PATH . '/Models/ProjectModel.php';

use MyApp\Models\Database;
use MyApp\Models\ProjectModel;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "Views/projects/list_projects.php");
    exit;
}

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db = $database->getConnection();
    
    $estimateModel = new EstimateModel($db);
    $projectModel = new ProjectModel($db);
    
    // Get project data
    $projectId = $_POST['project_id'] ?? null;
    if (!$projectId) {
        throw new Exception("Project ID is required");
    }
    
    $project = $projectModel->getProjectById($projectId);
    if (!$project) {
        throw new Exception("Project not found");
    }
    
    // Prepare project data for estimate
    $projectData = [
        'project_name' => $project['project_name'],
        'project_description' => $project['project_description'] ?? '',
        'customer_name' => $_POST['customer_name'] ?? null,
        'customer_email' => $_POST['customer_email'] ?? null,
        'customer_phone' => $_POST['customer_phone'] ?? null,
        'router_time' => $project['router_time'] ?? 0,
        'laser_time' => $project['laser_time'] ?? 0,
        'labor_hours' => $project['labor_hours'] ?? 0,
        'notes' => $_POST['notes'] ?? null
    ];
    
    // Prepare materials data
    $materialsData = [];
    if (isset($_POST['materials']) && is_array($_POST['materials'])) {
        foreach ($_POST['materials'] as $material) {
            if (!empty($material['material_name']) && !empty($material['quantity'])) {
                $materialsData[] = [
                    'material_name' => $material['material_name'],
                    'quantity' => floatval($material['quantity']),
                    'unit_type' => $material['unit_type'],
                    'unit_cost' => floatval($material['unit_cost']),
                    'total_cost' => floatval($material['total_cost']),
                    'notes' => $material['notes'] ?? null
                ];
            }
        }
    }
    
    // Prepare custom items data
    $customItems = [];
    if (isset($_POST['custom_items']) && is_array($_POST['custom_items'])) {
        foreach ($_POST['custom_items'] as $item) {
            if (!empty($item['item_name']) && !empty($item['cost'])) {
                $customItems[] = [
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'cost' => floatval($item['cost'])
                ];
            }
        }
    }
    
    // Determine if this is a project estimate (no customer)
    $isProjectEstimate = empty($projectData['customer_name']);
    
    // Create estimate from project
    $result = $estimateModel->createEstimateFromProject(
        $projectId,
        $projectData,
        $materialsData,
        $customItems,
        $isProjectEstimate
    );
    
    if ($result['success']) {
        // Redirect to view the newly created estimate
        header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $result['estimate_id'] . "&success=Estimate created successfully");
        exit;
    } else {
        throw new Exception("Failed to create estimate");
    }
    
} catch (Exception $e) {
    error_log("Error creating estimate from project: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/projects/list_projects.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
