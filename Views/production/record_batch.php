<?php
// Record Production Batch
// Allows recording production runs and updates inventory

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/ProjectModel.php';
require_once BASE_PATH . 'Models/ProductionModel.php';

use MyApp\Models\Database;
use MyApp\Models\ProjectModel;
use MyApp\Models\ProductionModel;

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Initialize database
$database = new Database();
$db = $database->getPdo();
$projectModel = new ProjectModel($db);
$productionModel = new ProductionModel($db);

// Handle form submission
$success = false;
$error = null;
$batchId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_batch') {
    
    // Get project and estimate to calculate costs
    $project = $projectModel->getProjectById($_POST['project_id']);
    $materialCost = 0;
    $laborCost = 0;
    
    if ($project && $project['estimate_id']) {
        // Get estimate costs
        $estimateQuery = "SELECT materials_cost, labor_cost, machine_cost, subtotal FROM estimates WHERE id = :estimate_id";
        $estimateStmt = $db->prepare($estimateQuery);
        $estimateStmt->execute([':estimate_id' => $project['estimate_id']]);
        $estimate = $estimateStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($estimate) {
            $quantityProduced = (int)$_POST['quantity_produced'];
            
            // Calculate per-unit costs from estimate
            // Material cost per unit = materials_cost from estimate
            $materialCost = $estimate['materials_cost'] ?? 0;
            
            // Labor cost per unit = labor_cost + machine_cost from estimate
            $laborCost = ($estimate['labor_cost'] ?? 0) + ($estimate['machine_cost'] ?? 0);
            
            // Multiply by quantity produced for batch total
            $materialCost = $materialCost * $quantityProduced;
            $laborCost = $laborCost * $quantityProduced;
        }
    }
    
    $data = [
        'project_id' => $_POST['project_id'],
        'batch_number' => $_POST['batch_number'] ?? null,
        'quantity_produced' => (int)$_POST['quantity_produced'],
        'production_date' => $_POST['production_date'],
        'labor_hours' => !empty($_POST['labor_hours']) ? (float)$_POST['labor_hours'] : null,
        'laser_time' => !empty($_POST['laser_time']) ? (float)$_POST['laser_time'] : null,
        'mill_time' => !empty($_POST['mill_time']) ? (float)$_POST['mill_time'] : null,
        'material_cost' => $materialCost,
        'labor_cost' => $laborCost,
        'notes' => $_POST['notes'] ?? null,
        'produced_by' => $_SESSION['username']
    ];
    
    $batchId = $productionModel->recordProductionBatch($data);
    
    if ($batchId) {
        $success = true;
        $_SESSION['success_message'] = "Production batch recorded successfully! Inventory updated.";
        
        // Update project status to 'active' if this is first production
        $project = $projectModel->getProjectById($data['project_id']);
        if ($project && $project['production_status'] === 'ready') {
            $productionModel->updateProductionStatus($data['project_id'], 'active');
        }
    } else {
        $error = "Failed to record production batch. Please try again.";
    }
}

// Get projects ready for production (ready or active status only)
$projectsQuery = "SELECT id, project_name, inventory_quantity 
                  FROM projects 
                  WHERE production_status IN ('ready', 'active') 
                  ORDER BY project_name ASC";
$projectsStmt = $db->prepare($projectsQuery);
$projectsStmt->execute();
$allProjects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Generate suggested batch number for today
$suggestedBatchNumber = $productionModel->generateBatchNumber(date('Y-m-d'));

// Get recent batches
$recentBatches = [];
$recentQuery = "SELECT pb.*, p.project_name 
                FROM production_batches pb
                INNER JOIN projects p ON pb.project_id = p.id
                ORDER BY pb.production_date DESC, pb.created_at DESC
                LIMIT 10";
$recentStmt = $db->query($recentQuery);
$recentBatches = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Get low stock projects
$lowStock = $productionModel->getLowStockProjects();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Production Batch - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .low-stock-alert {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .low-stock-alert h4 {
            margin: 0 0 0.5rem 0;
            color: #92400e;
            font-size: 0.95rem;
        }
        .low-stock-item {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
            border-bottom: 1px solid #fde68a;
            font-size: 0.875rem;
        }
        .low-stock-item:last-child {
            border-bottom: none;
        }
        .production-form {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 0.75rem;
        }
        .production-form .form-group {
            margin-bottom: 0;
        }
        .production-form .form-group.col-6 { grid-column: span 6; }
        .production-form .form-group.col-4 { grid-column: span 4; }
        .production-form .form-group.col-3 { grid-column: span 3; }
        .production-form .form-group.col-2 { grid-column: span 2; }
        .production-form .form-group.col-12 { grid-column: span 12; }
        .production-form .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        .production-form .form-control {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        .production-form input[type="number"].narrow {
            max-width: 120px;
        }
        .production-form input[type="date"].narrow {
            max-width: 160px;
        }
        .cost-summary {
            background: #f3f4f6;
            padding: 0.75rem;
            border-radius: 4px;
            margin-top: 0.75rem;
            font-size: 0.875rem;
        }
        .cost-summary h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.95rem;
        }
        .cost-summary .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
        }
        .cost-summary .summary-row.total {
            border-top: 2px solid #d1d5db;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            font-weight: 600;
        }
        .batch-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        .batch-table th,
        .batch-table td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .batch-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .batch-table tr:hover {
            background: #f9fafb;
        }
        .page-header {
            margin-bottom: 1rem;
        }
        .page-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        .page-header p {
            font-size: 0.875rem;
        }
        .card {
            margin-bottom: 1rem;
        }
        .card-body {
            padding: 1rem;
        }
    </style>
</head>
<body>
    <?php require_once BASE_PATH . 'Views/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Record Production Batch</h1>
            <p class="page-subtitle">Track production runs and update inventory</p>
        </div>

        <?php if ($success): ?>
            <div class="notification notification-success mb-4">
                ✅ <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="notification notification-error mb-4">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Low Stock Alert -->
        <?php if (!empty($lowStock)): ?>
            <div class="low-stock-alert">
                <h4>⚠️ Low Stock Alert - These projects need production:</h4>
                <?php foreach ($lowStock as $item): ?>
                    <div class="low-stock-item">
                        <span><strong><?php echo htmlspecialchars($item['project_name']); ?></strong></span>
                        <span>
                            Stock: <?php echo $item['inventory_quantity']; ?> / 
                            Reorder: <?php echo $item['reorder_point']; ?> 
                            (Need <?php echo $item['units_needed']; ?> units)
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Production Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">New Production Batch</h2>
                <div style="margin-left: auto;">
                    <a href="<?php echo BASE_URL; ?>Views/production/print_production_report.php?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" 
                       target="_blank" 
                       class="btn btn-outline btn-sm" 
                       title="Print Production Report">
                        🖨️ Production Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>Views/production/inventory_dashboard.php" 
                       class="btn btn-outline btn-sm" 
                       title="View Inventory Dashboard">
                        📦 Inventory
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" id="productionForm">
                    <input type="hidden" name="action" value="record_batch">
                    
                    <div class="production-form">
                        <!-- Row 1: Project and Batch Number -->
                        <div class="form-group col-6">
                            <label for="project_id" class="form-label required">Project</label>
                            <select name="project_id" id="project_id" class="form-control" required>
                                <option value="">-- Select Project --</option>
                                <?php foreach ($allProjects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>">
                                        <?php echo htmlspecialchars($project['project_name']); ?>
                                        (Stock: <?php echo $project['inventory_quantity'] ?? 0; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group col-3">
                            <label for="batch_number" class="form-label">
                                Batch # <small style="color: #6b7280;">(auto)</small>
                            </label>
                            <input type="text" name="batch_number" id="batch_number" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($suggestedBatchNumber); ?>"
                                   placeholder="<?php echo htmlspecialchars($suggestedBatchNumber); ?>">
                        </div>
                        
                        <div class="form-group col-3">
                            <label for="production_date" class="form-label required">Production Date</label>
                            <input type="date" name="production_date" id="production_date" 
                                   class="form-control narrow" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Row 2: Quantity and Machine Times -->
                        <div class="form-group col-2">
                            <label for="quantity_produced" class="form-label required">Quantity</label>
                            <input type="number" name="quantity_produced" id="quantity_produced" 
                                   class="form-control narrow" min="1" required placeholder="Units made">
                        </div>
                        
                        <div class="form-group col-2">
                            <label for="labor_hours" class="form-label">Labor Hrs</label>
                            <input type="number" name="labor_hours" id="labor_hours" 
                                   class="form-control narrow" step="0.25" placeholder="0">
                        </div>
                        
                        <div class="form-group col-2">
                            <label for="laser_time" class="form-label">Laser Mins</label>
                            <input type="number" name="laser_time" id="laser_time" 
                                   class="form-control narrow" step="1" placeholder="0">
                        </div>
                        
                        <div class="form-group col-2">
                            <label for="mill_time" class="form-label">Mill Mins</label>
                            <input type="number" name="mill_time" id="mill_time" 
                                   class="form-control narrow" step="1" placeholder="0">
                        </div>
                        
                        <div class="form-group col-2" style="display: flex; align-items: flex-end;">
                            <div class="cost-summary" id="costSummary" style="display: none; width: 100%; margin-top: 0; padding: 0.5rem;">
                                <div class="summary-row" style="font-size: 0.8125rem;">
                                    <span>Time/Piece:</span>
                                    <span id="summaryTimePerPiece" style="font-weight: 600;">0.00 hrs</span>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Notes -->
                        <div class="form-group col-12">
                            <label for="notes" class="form-label">Production Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" 
                                      placeholder="Optional: quality notes, issues, observations..."></textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Record Production Batch</button>
                        <a href="<?php echo BASE_URL; ?>Views/production/inventory_dashboard.php" class="btn btn-secondary">
                            View Inventory Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Batches -->
        <?php if (!empty($recentBatches)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Recent Production Batches</h2>
                </div>
                <div class="card-body">
                    <table class="batch-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Project</th>
                                <th>Batch #</th>
                                <th>Quantity</th>
                                <th>Cost/Unit</th>
                                <th>Total Cost</th>
                                <th>Produced By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBatches as $batch): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($batch['production_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($batch['project_name']); ?></td>
                                    <td><?php echo htmlspecialchars($batch['batch_number'] ?? '-'); ?></td>
                                    <td><?php echo $batch['quantity_produced']; ?> units</td>
                                    <td>
                                        <?php echo $batch['cost_per_unit'] ? '$' . number_format($batch['cost_per_unit'], 2) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $totalCost = ($batch['material_cost'] ?? 0) + ($batch['labor_cost'] ?? 0);
                                        echo $totalCost > 0 ? '$' . number_format($totalCost, 2) : '-';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($batch['produced_by'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Calculate time per piece based on batch quantity
        function updateCostSummary() {
            const batchQty = parseFloat(document.getElementById('quantity_produced').value) || 1;
            const laborHours = parseFloat(document.getElementById('labor_hours').value) || 0;
            const laserMins = parseFloat(document.getElementById('laser_time').value) || 0;
            const millMins = parseFloat(document.getElementById('mill_time').value) || 0;
            
            // Convert minutes to hours for calculation
            const laserHours = laserMins / 60;
            const millHours = millMins / 60;
            
            const totalHours = laborHours + laserHours + millHours;
            
            // Calculate time per piece (total time divided by batch quantity)
            const timePerPiece = batchQty > 0 ? totalHours / batchQty : 0;
            
            document.getElementById('summaryTimePerPiece').textContent = timePerPiece.toFixed(4) + ' hrs';
            
            // Show summary if any hours entered
            const summary = document.getElementById('costSummary');
            if (totalHours > 0) {
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
            }
        }
        
        // Attach event listeners
        document.getElementById('quantity_produced').addEventListener('input', updateCostSummary);
        document.getElementById('labor_hours').addEventListener('input', updateCostSummary);
        document.getElementById('laser_time').addEventListener('input', updateCostSummary);
        document.getElementById('mill_time').addEventListener('input', updateCostSummary);
        
        // Update batch number when production date changes
        document.getElementById('production_date').addEventListener('change', async function() {
            const dateInput = this.value;
            if (!dateInput) return;
            
            // Format date to YYYYMMDD
            const date = new Date(dateInput + 'T00:00:00'); // Add time to avoid timezone issues
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const datePrefix = year + month + day;
            
            // Fetch next batch number via AJAX
            try {
                const response = await fetch('<?php echo BASE_URL; ?>Views/production/get_next_batch.php?date=' + dateInput);
                const data = await response.json();
                
                if (data.batch_number) {
                    document.getElementById('batch_number').value = data.batch_number;
                    document.getElementById('batch_number').placeholder = data.batch_number;
                }
            } catch (error) {
                console.error('Error fetching batch number:', error);
                // Fallback: just use date prefix + -1
                document.getElementById('batch_number').value = datePrefix + '-1';
            }
        });
    </script>
</body>
</html>
