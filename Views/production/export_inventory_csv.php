<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

// Get filter parameters
$showLowStock = $_GET['low_stock'] ?? false;
$productionStatus = $_GET['status'] ?? null;

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $pdo = $database->getPdo();
    
    // Build query
    $sql = "SELECT 
                p.id,
                p.project_name,
                COALESCE(p.production_status, 'design') as production_status,
                COALESCE(p.inventory_quantity, 0) as inventory_quantity,
                COALESCE(p.reorder_point, 5) as reorder_point,
                COALESCE(p.batch_size, 10) as batch_size,
                p.cost_per_unit,
                p.last_inventory_sync,
                (SELECT COUNT(*) FROM production_batches WHERE project_id = p.id) as total_batches,
                (SELECT SUM(quantity_produced) FROM production_batches WHERE project_id = p.id) as total_produced
            FROM projects p
            WHERE (p.production_status IN ('ready', 'active') 
                   OR p.inventory_quantity > 0 
                   OR EXISTS (SELECT 1 FROM production_batches WHERE project_id = p.id))";
    
    $params = [];
    
    if ($showLowStock) {
        $sql .= " AND p.inventory_quantity <= p.reorder_point";
    }
    
    if ($productionStatus) {
        $sql .= " AND p.production_status = :status";
        $params[':status'] = $productionStatus;
    }
    
    $sql .= " ORDER BY 
                CASE 
                    WHEN p.inventory_quantity <= p.reorder_point THEN 0 
                    ELSE 1 
                END,
                p.inventory_quantity ASC,
                p.project_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    $filename = "inventory_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header row
    fputcsv($output, [
        'Project ID',
        'Project Name',
        'Production Status',
        'Inventory Quantity',
        'Reorder Point',
        'Batch Size',
        'Cost Per Unit',
        'Inventory Value',
        'Total Batches Produced',
        'Total Units Produced',
        'Low Stock Alert',
        'Last Sync'
    ]);
    
    // Write data rows
    foreach ($projects as $project) {
        $inventoryValue = $project['inventory_quantity'] * ($project['cost_per_unit'] ?? 0);
        $isLowStock = $project['inventory_quantity'] <= $project['reorder_point'];
        
        fputcsv($output, [
            $project['id'],
            $project['project_name'],
            ucfirst($project['production_status']),
            $project['inventory_quantity'],
            $project['reorder_point'],
            $project['batch_size'],
            $project['cost_per_unit'] ? number_format($project['cost_per_unit'], 2) : '',
            number_format($inventoryValue, 2),
            $project['total_batches'] ?? 0,
            $project['total_produced'] ?? 0,
            $isLowStock ? 'YES' : 'NO',
            $project['last_inventory_sync'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log("Error exporting inventory data: " . $e->getMessage());
    die("Error exporting inventory report: " . $e->getMessage());
}
