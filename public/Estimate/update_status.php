<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

$estimateId = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($estimateId && $status) {
    try {
        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn = $database->getPdo();
        $estimateModel = new EstimateModel($conn);
        
        $estimateModel->updateStatus($estimateId, $status);
        
        header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $estimateId . "&status_updated=1");
        exit();
    } catch (Exception $e) {
        error_log("Error updating status: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/estimate/view_estimate.php?id=" . $estimateId . "&error=status_update_failed");
        exit();
    }
} else {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php");
    exit();
}
?>