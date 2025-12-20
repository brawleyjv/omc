<?php
// Clone an existing estimate (for creating customer-specific versions of base estimates)
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

$estimateId = $_GET['id'] ?? null;

if (!$estimateId) {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php?error=No+estimate+specified");
    exit();
}

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    $estimateModel = new EstimateModel($conn);
    
    // Get the original estimate
    $original = $estimateModel->getEstimateById($estimateId);
    
    if (!$original) {
        throw new Exception("Estimate not found");
    }
    
    // Redirect to edit page with clone parameter
    header("Location: " . BASE_URL . "Views/estimate/edit_estimate.php?id=" . $estimateId . "&clone=1");
    exit();
    
} catch (Exception $e) {
    error_log("Error cloning estimate: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $estimateId . "&error=" . urlencode($e->getMessage()));
    exit();
}
?>
