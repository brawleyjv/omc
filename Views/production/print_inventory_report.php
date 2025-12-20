<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/ProductionModel.php';
require_once BASE_PATH . '/Models/Settings.php';

use MyApp\Models\Database;
use MyApp\Models\ProductionModel;
use Models\Settings;

// Get company settings
$settingsModel = new Settings();
$companySettings = $settingsModel->getSettings();

$company_name = $companySettings['company_name'] ?? 'Your Company Name';
$company_slogan = $companySettings['company_slogan'] ?? '';
$company_address = $companySettings['company_address'] ?? '';
$company_city = $companySettings['company_city'] ?? '';
$company_state = $companySettings['company_state'] ?? '';
$company_zip = $companySettings['company_zip'] ?? '';
$company_phone = $companySettings['company_phone'] ?? '';
$company_email = $companySettings['company_email'] ?? '';

// Format address
$full_address = '';
if (!empty($company_address)) {
    $full_address = $company_address;
    if (!empty($company_city) || !empty($company_state) || !empty($company_zip)) {
        $full_address .= ', ';
        if (!empty($company_city)) $full_address .= $company_city . ', ';
        if (!empty($company_state)) $full_address .= $company_state . ' ';
        if (!empty($company_zip)) $full_address .= $company_zip;
    }
}

// Get filter parameters
$showLowStock = $_GET['low_stock'] ?? false; // Show only low stock items
$productionStatus = $_GET['status'] ?? null; // Filter by production status

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $pdo = $database->getPdo();
    
    // Build query - Only show projects that are in production (not just in design phase)
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
    
    // Calculate totals
    $totalProjects = count($projects);
    $totalUnits = 0;
    $totalValue = 0;
    $lowStockCount = 0;
    
    foreach ($projects as $project) {
        $totalUnits += $project['inventory_quantity'];
        $totalValue += $project['inventory_quantity'] * ($project['cost_per_unit'] ?? 0);
        if ($project['inventory_quantity'] <= $project['reorder_point']) {
            $lowStockCount++;
        }
    }
    
} catch (Exception $e) {
    error_log("Error loading inventory data: " . $e->getMessage());
    die("Error loading inventory report: " . $e->getMessage() . "<br><br>Query: " . ($sql ?? 'N/A'));
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Report - <?php echo date('M d, Y'); ?></title>
    <style>
        @page { 
            margin: 10mm 15mm; 
            size: letter;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #000;
            line-height: 1.3;
            font-size: 9pt;
            margin: 0;
            padding: 15px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .company-slogan {
            font-size: 9pt;
            color: #555;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 8pt;
            line-height: 1.4;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .report-subtitle {
            font-size: 10pt;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
        }
        th {
            background-color: #333;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        td {
            padding: 5px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-row {
            background-color: #e8e8e8 !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .summary-box {
            margin-top: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 2px solid #333;
        }
        .summary-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 8pt;
            color: #555;
        }
        .summary-value {
            font-size: 14pt;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-ready {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-design {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .status-discontinued {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .low-stock {
            background-color: #fef3c7 !important;
        }
        .stock-bar {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 2px;
        }
        .stock-bar-fill {
            height: 100%;
            background-color: #10b981;
            transition: width 0.3s;
        }
        .stock-bar-fill.low {
            background-color: #f59e0b;
        }
        .stock-bar-fill.critical {
            background-color: #ef4444;
        }
        .no-print {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls (hidden when printing) -->
    <div class="no-print">
        <button onclick="window.print()" style="padding: 8px 16px; font-size: 11pt; cursor: pointer;">🖨️ Print Report</button>
        <a href="<?php echo BASE_URL; ?>Views/reports/index.php" style="padding: 8px 16px; font-size: 11pt; cursor: pointer; margin-left: 10px; text-decoration: none; background: #fff; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">✖ Close</a>
        <div style="margin-top: 10px; font-size: 9pt;">
            <strong>Filters:</strong>
            <a href="?">All Projects</a> |
            <a href="?low_stock=1">Low Stock Only</a> |
            <a href="?status=active">Active</a> |
            <a href="?status=ready">Ready</a>
        </div>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <?php if ($company_slogan): ?>
                <div class="company-slogan"><?php echo htmlspecialchars($company_slogan); ?></div>
            <?php endif; ?>
            <div class="company-info">
                <?php if ($full_address): ?>
                    <?php echo htmlspecialchars($full_address); ?><br>
                <?php endif; ?>
                <?php if ($company_phone): ?>
                    <?php echo htmlspecialchars($company_phone); ?>
                <?php endif; ?>
                <?php if ($company_email): ?>
                    <?php if ($company_phone) echo ' • '; ?>
                    <?php echo htmlspecialchars($company_email); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-right">
            <div class="report-title">INVENTORY REPORT</div>
            <div class="report-subtitle">
                As of <?php echo date('M d, Y g:i A'); ?>
            </div>
            <?php if ($showLowStock): ?>
                <div style="margin-top: 5px; font-size: 8pt; color: #dc2626; font-weight: bold;">
                    ⚠ LOW STOCK ITEMS ONLY
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-title">Inventory Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Active Projects</div>
                <div class="summary-value"><?php echo $totalProjects; ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Units</div>
                <div class="summary-value"><?php echo number_format($totalUnits); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Value</div>
                <div class="summary-value">$<?php echo number_format($totalValue, 2); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Low Stock Items</div>
                <div class="summary-value" style="color: <?php echo $lowStockCount > 0 ? '#dc2626' : '#10b981'; ?>">
                    <?php echo $lowStockCount; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 35%;">Project Name</th>
                <th style="width: 8%;" class="text-center">Status</th>
                <th style="width: 8%;" class="text-center">On Hand</th>
                <th style="width: 8%;" class="text-center">Reorder</th>
                <th style="width: 12%;">Stock Level</th>
                <th style="width: 8%;" class="text-right">Cost/Unit</th>
                <th style="width: 10%;" class="text-right">Total Value</th>
                <th style="width: 8%;" class="text-center">Batches</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #999;">
                        No inventory items found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <?php
                    $isLowStock = $project['inventory_quantity'] <= $project['reorder_point'];
                    $stockPercentage = $project['reorder_point'] > 0 
                        ? min(100, ($project['inventory_quantity'] / ($project['reorder_point'] * 2)) * 100)
                        : 100;
                    $barClass = '';
                    if ($stockPercentage <= 50) $barClass = 'critical';
                    elseif ($stockPercentage <= 100) $barClass = 'low';
                    
                    $itemValue = $project['inventory_quantity'] * ($project['cost_per_unit'] ?? 0);
                    ?>
                    <tr class="<?php echo $isLowStock ? 'low-stock' : ''; ?>">
                        <td><strong><?php echo htmlspecialchars($project['project_name']); ?></strong></td>
                        <td class="text-center">
                            <span class="status-badge status-<?php echo htmlspecialchars($project['production_status']); ?>">
                                <?php echo ucfirst($project['production_status']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <strong><?php echo number_format($project['inventory_quantity']); ?></strong>
                        </td>
                        <td class="text-center"><?php echo number_format($project['reorder_point']); ?></td>
                        <td>
                            <?php if ($isLowStock): ?>
                                <strong style="color: #dc2626;">⚠ LOW STOCK</strong>
                            <?php else: ?>
                                <span style="color: #10b981;">✓ In Stock</span>
                            <?php endif; ?>
                            <div class="stock-bar">
                                <div class="stock-bar-fill <?php echo $barClass; ?>" 
                                     style="width: <?php echo $stockPercentage; ?>%;"></div>
                            </div>
                        </td>
                        <td class="text-right">
                            <?php echo $project['cost_per_unit'] ? '$' . number_format($project['cost_per_unit'], 2) : '-'; ?>
                        </td>
                        <td class="text-right">
                            <strong>$<?php echo number_format($itemValue, 2); ?></strong>
                        </td>
                        <td class="text-center"><?php echo number_format($project['total_batches'] ?? 0); ?></td>
                    </tr>
                <?php endforeach; ?>
                <!-- Totals Row -->
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTALS:</td>
                    <td class="text-center"><?php echo number_format($totalUnits); ?></td>
                    <td colspan="3"></td>
                    <td class="text-right">$<?php echo number_format($totalValue, 2); ?></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($lowStockCount > 0 && !$showLowStock): ?>
        <div style="margin-top: 15px; padding: 10px; background-color: #fef3c7; border: 2px solid #f59e0b; border-radius: 4px;">
            <strong style="color: #92400e;">⚠ Action Required:</strong> 
            <span style="color: #78350f;"><?php echo $lowStockCount; ?> item<?php echo $lowStockCount !== 1 ? 's' : ''; ?> at or below reorder point.</span>
        </div>
    <?php endif; ?>

    <div style="margin-top: 20px; font-size: 7pt; color: #999; text-align: center;">
        Inventory Report • <?php echo htmlspecialchars($company_name); ?> • Page 1
    </div>
</body>
</html>
