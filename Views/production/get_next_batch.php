<?php
// AJAX endpoint to get next batch number for a given date
// Returns JSON: {"batch_number": "20251219-1"}

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/ProductionModel.php';

use MyApp\Models\Database;
use MyApp\Models\ProductionModel;

header('Content-Type: application/json');

// Check if date parameter provided
if (!isset($_GET['date'])) {
    echo json_encode(['error' => 'Date parameter required']);
    exit;
}

$date = $_GET['date'];

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

try {
    // Initialize database and model
    $database = new Database();
    $db = $database->getPdo();
    $productionModel = new ProductionModel($db);
    
    // Generate batch number
    $batchNumber = $productionModel->generateBatchNumber($date);
    
    echo json_encode([
        'success' => true,
        'batch_number' => $batchNumber,
        'date' => $date
    ]);
    
} catch (Exception $e) {
    error_log('Error generating batch number: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Failed to generate batch number',
        'message' => $e->getMessage()
    ]);
}
