<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

// Get date range from query parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $pdo = $database->getPdo();
    
    // Build query
    $sql = "SELECT pb.*, p.project_name, p.id as project_id_ref
            FROM production_batches pb
            JOIN projects p ON pb.project_id = p.id
            WHERE pb.production_date BETWEEN :start_date AND :end_date
            ORDER BY pb.production_date DESC, pb.batch_number DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    $filename = "production_report_" . $startDate . "_to_" . $endDate . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header row
    fputcsv($output, [
        'Production Date',
        'Batch Number',
        'Project Name',
        'Project ID',
        'Quantity Produced',
        'Labor Hours',
        'Laser Time (mins)',
        'Mill Time (mins)',
        'Total Time (hrs)',
        'Time Per Unit (hrs)',
        'Material Cost',
        'Labor Cost',
        'Cost Per Unit',
        'Produced By',
        'Notes',
        'Created At'
    ]);
    
    // Write data rows
    foreach ($batches as $batch) {
        $totalTime = ($batch['labor_hours'] ?? 0) + 
                    (($batch['laser_time'] ?? 0) + ($batch['mill_time'] ?? 0)) / 60;
        $timePerUnit = $batch['quantity_produced'] > 0 ? 
                      $totalTime / $batch['quantity_produced'] : 0;
        
        fputcsv($output, [
            $batch['production_date'],
            $batch['batch_number'] ?? '',
            $batch['project_name'],
            $batch['project_id'],
            $batch['quantity_produced'],
            $batch['labor_hours'] ?? 0,
            $batch['laser_time'] ?? 0,
            $batch['mill_time'] ?? 0,
            number_format($totalTime, 4),
            number_format($timePerUnit, 4),
            $batch['material_cost'] ?? 0,
            $batch['labor_cost'] ?? 0,
            $batch['cost_per_unit'] ?? 0,
            $batch['produced_by'] ?? '',
            $batch['notes'] ?? '',
            $batch['created_at']
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log("Error exporting production data: " . $e->getMessage());
    die("Error exporting production report: " . $e->getMessage());
}
