<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include config and models
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

// Check if ID is provided
$estimateId = $_GET['id'] ?? null;

if (!$estimateId) {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php?error=No+estimate+ID+provided");
    exit();
}

try {
    // Establish database connection
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete custom items first
    $deleteCustomItems = $conn->prepare("DELETE FROM estimate_custom_items WHERE estimate_id = :estimate_id");
    $deleteCustomItems->execute([':estimate_id' => $estimateId]);
    
    // Delete materials
    $deleteMaterials = $conn->prepare("DELETE FROM estimate_materials WHERE estimate_id = :estimate_id");
    $deleteMaterials->execute([':estimate_id' => $estimateId]);
    
    // Delete the estimate
    $deleteEstimate = $conn->prepare("DELETE FROM estimates WHERE id = :estimate_id");
    $deleteEstimate->execute([':estimate_id' => $estimateId]);
    
    // Commit transaction
    $conn->commit();
    
    // Redirect with success message
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php?deleted=1");
    exit();
    
} catch (Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error deleting estimate: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
