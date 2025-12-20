<?php
// Inventory Dashboard
// View all inventory levels, low stock alerts, and recent transactions

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

// Get inventory summary
$summary = $productionModel->getInventorySummary();

// Get low stock projects
$lowStock = $productionModel->getLowStockProjects();

// Get all projects with inventory tracking
$inventoryQuery = "SELECT 
                    id, project_name, production_status,
                    inventory_quantity, reorder_point, batch_size,
                    cost_per_unit, last_inventory_sync
                   FROM projects
                   WHERE production_status IN ('ready', 'active')
                   ORDER BY production_status, project_name";
$inventoryStmt = $db->query($inventoryQuery);
$inventoryProjects = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent inventory transactions
$recentTransactions = [];
$transQuery = "SELECT it.*, p.project_name
               FROM inventory_transactions it
               INNER JOIN projects p ON it.project_id = p.id
               ORDER BY it.created_at DESC
               LIMIT 20";
$transStmt = $db->query($transQuery);
$recentTransactions = $transStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .stat-card h3 {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0 0 0.5rem 0;
            font-weight: 500;
        }
        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
        }
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .inventory-table th,
        .inventory-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .inventory-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            position: sticky;
            top: 0;
        }
        .inventory-table tr:hover {
            background: #f9fafb;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-ready { background: #dbeafe; color: #1e40af; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-low { background: #fee2e2; color: #991b1b; }
        .stock-bar {
            width: 100%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 0.25rem;
        }
        .stock-bar-fill {
            height: 100%;
            background: #10b981;
            transition: width 0.3s;
        }
        .stock-bar-fill.low { background: #ef4444; }
        .stock-bar-fill.medium { background: #f59e0b; }
        .transaction-type {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .type-production { background: #d1fae5; color: #065f46; }
        .type-sale { background: #fee2e2; color: #991b1b; }
        .type-adjustment { background: #dbeafe; color: #1e40af; }
        .card-body { padding: 1rem; }
        .page-header { margin-bottom: 1rem; }
        .page-header h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .page-header p { font-size: 0.875rem; }
    </style>
</head>
<body>
    <?php require_once BASE_PATH . 'Views/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Inventory Dashboard</h1>
            <p class="page-subtitle">Track stock levels, production batches, and inventory movements</p>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <?php 
            $totalProjects = count($inventoryProjects);
            $lowStockCount = count($lowStock);
            $totalUnits = array_sum(array_column($inventoryProjects, 'inventory_quantity'));
            ?>
            <div class="stat-card">
                <h3>Active Projects</h3>
                <div class="value"><?php echo $totalProjects; ?></div>
            </div>
            <div class="stat-card">
                <h3>Low Stock Alert</h3>
                <div class="value" style="color: <?php echo $lowStockCount > 0 ? '#ef4444' : '#10b981'; ?>">
                    <?php echo $lowStockCount; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Total Inventory</h3>
                <div class="value"><?php echo number_format($totalUnits); ?> units</div>
            </div>
            <div class="stat-card">
                <h3>Quick Actions</h3>
                <a href="<?php echo BASE_URL; ?>Views/production/record_batch.php" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">
                    Record Production
                </a>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <?php if (!empty($lowStock)): ?>
            <div class="card mb-4" style="border-left: 4px solid #ef4444;">
                <div class="card-header" style="background: #fee2e2;">
                    <h2 class="card-title" style="color: #991b1b;">⚠️ Low Stock Alert (<?php echo count($lowStock); ?> items)</h2>
                </div>
                <div class="card-body">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Current Stock</th>
                                <th>Reorder Point</th>
                                <th>Batch Size</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStock as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['project_name']); ?></strong></td>
                                    <td>
                                        <strong style="color: #ef4444;"><?php echo $item['inventory_quantity']; ?></strong> units
                                    </td>
                                    <td><?php echo $item['reorder_point']; ?> units</td>
                                    <td><?php echo $item['batch_size']; ?> units</td>
                                    <td>
                                        <span style="color: #6b7280; font-size: 0.875rem;">
                                            Need <?php echo $item['units_needed']; ?> more
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- All Inventory -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Inventory Levels</h2>
                <div style="margin-left: auto;">
                    <a href="<?php echo BASE_URL; ?>Views/production/print_inventory_report.php" 
                       target="_blank" 
                       class="btn btn-primary btn-sm" 
                       title="Print Full Inventory Report">
                        🖨️ Print Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>Views/production/print_inventory_report.php?low_stock=1" 
                       target="_blank" 
                       class="btn btn-outline btn-sm" 
                       title="Print Low Stock Report">
                        ⚠️ Low Stock Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>Views/production/record_batch.php" 
                       class="btn btn-outline btn-sm" 
                       title="Record Production Batch">
                        🏭 Record Batch
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Stock Level</th>
                            <th>Reorder Point</th>
                            <th>Batch Size</th>
                            <th>Cost/Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventoryProjects as $project): ?>
                            <?php 
                            $stock = $project['inventory_quantity'];
                            $reorder = $project['reorder_point'];
                            $isLow = $stock <= $reorder;
                            $percentage = $reorder > 0 ? min(100, ($stock / ($reorder * 2)) * 100) : 100;
                            $barClass = $stock <= $reorder ? 'low' : ($stock <= $reorder * 1.5 ? 'medium' : '');
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($project['project_name']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $project['production_status']; ?>">
                                        <?php echo ucfirst($project['production_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="min-width: 120px;">
                                        <strong><?php echo $stock; ?></strong> units
                                        <div class="stock-bar">
                                            <div class="stock-bar-fill <?php echo $barClass; ?>" 
                                                 style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $reorder; ?> units</td>
                                <td><?php echo $project['batch_size']; ?> units</td>
                                <td>
                                    <?php echo $project['cost_per_unit'] ? '$' . number_format($project['cost_per_unit'], 2) : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <?php if (!empty($recentTransactions)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Recent Inventory Transactions</h2>
                </div>
                <div class="card-body">
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Before</th>
                                <th>After</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $trans): ?>
                                <tr>
                                    <td><?php echo date('M j, Y g:i A', strtotime($trans['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($trans['project_name']); ?></td>
                                    <td>
                                        <span class="transaction-type type-<?php echo $trans['transaction_type']; ?>">
                                            <?php echo ucfirst($trans['transaction_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: <?php echo $trans['quantity'] > 0 ? '#10b981' : '#ef4444'; ?>">
                                            <?php echo $trans['quantity'] > 0 ? '+' : ''; ?><?php echo $trans['quantity']; ?>
                                        </strong>
                                    </td>
                                    <td><?php echo $trans['quantity_before']; ?></td>
                                    <td><?php echo $trans['quantity_after']; ?></td>
                                    <td style="font-size: 0.8125rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($trans['notes'] ?? '-'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
