<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EtsyModel.php';

use MyApp\Models\Database;
use MyApp\Models\EtsyModel;

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "public/login.php");
    exit();
}

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$pdo = $database->getPdo();
$etsyModel = new EtsyModel($pdo);
$etsyConnected = $etsyModel->isConnected();

// Check if Etsy is connected
if (!$etsyConnected) {
    header("Location: " . BASE_URL . "Views/reports/index.php");
    exit();
}

// Get date range from URL or default to last 30 days
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get production vs sales comparison data
$comparisonQuery = "
    SELECT 
        p.id as project_id,
        p.project_name,
        p.production_status,
        p.inventory_quantity,
        p.reorder_point,
        
        -- Production data
        COUNT(DISTINCT pb.id) as batches_produced,
        COALESCE(SUM(pb.quantity_produced), 0) as units_produced,
        
        -- Sales data
        COALESCE(SUM(oi.quantity), 0) as units_sold,
        COALESCE(SUM(oi.total_price), 0) as revenue,
        COUNT(DISTINCT o.id) as orders,
        
        -- Inventory turnover
        p.inventory_quantity as current_stock,
        COALESCE(SUM(pb.quantity_produced), 0) - COALESCE(SUM(oi.quantity), 0) - p.inventory_quantity as variance,
        
        -- Dates
        MIN(pb.production_date) as first_production,
        MAX(pb.production_date) as last_production,
        MIN(o.order_date) as first_sale,
        MAX(o.order_date) as last_sale
        
    FROM projects p
    LEFT JOIN production_batches pb ON p.id = pb.project_id 
        AND pb.production_date BETWEEN :start_date AND :end_date
    LEFT JOIN etsy_order_items oi ON p.id = oi.project_id
    LEFT JOIN etsy_orders o ON oi.etsy_order_id = o.id 
        AND o.order_date BETWEEN :start_date AND :end_date
        AND o.status != 'cancelled'
    
    WHERE p.production_status IN ('ready', 'active')
    GROUP BY p.id, p.project_name, p.production_status, p.inventory_quantity, p.reorder_point
    HAVING units_produced > 0 OR units_sold > 0
    ORDER BY revenue DESC, units_produced DESC
";

$comparisonStmt = $pdo->prepare($comparisonQuery);
$comparisonStmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$comparisonData = $comparisonStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate summary metrics
$totalProduced = 0;
$totalSold = 0;
$totalRevenue = 0;
$producedNotSold = 0;
$overproducedCount = 0;
$underproducedCount = 0;

foreach ($comparisonData as $row) {
    $totalProduced += $row['units_produced'];
    $totalSold += $row['units_sold'];
    $totalRevenue += $row['revenue'];
    
    if ($row['units_produced'] > $row['units_sold']) {
        $producedNotSold += ($row['units_produced'] - $row['units_sold']);
        $overproducedCount++;
    } elseif ($row['units_sold'] > $row['units_produced']) {
        $underproducedCount++;
    }
}

$sellThroughRate = $totalProduced > 0 ? ($totalSold / $totalProduced) * 100 : 0;

include __DIR__ . '/../header.php';
?>

<style>
    .report-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .report-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .report-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .date-filter {
        display: flex;
        gap: 1rem;
        align-items: center;
        background: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .date-filter label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
    }
    
    .date-filter input[type="date"] {
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.875rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #64748b;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #475569;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
    }
    
    .quick-filters {
        display: flex;
        gap: 0.5rem;
    }
    
    .quick-filters button {
        padding: 0.375rem 0.75rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.875rem;
        cursor: pointer;
    }
    
    .quick-filters button:hover {
        background: #e2e8f0;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .summary-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .summary-card.primary { border-left: 4px solid #3b82f6; }
    .summary-card.success { border-left: 4px solid #10b981; }
    .summary-card.warning { border-left: 4px solid #f59e0b; }
    .summary-card.danger { border-left: 4px solid #ef4444; }
    .summary-card.info { border-left: 4px solid #06b6d4; }
    
    .summary-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .summary-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .summary-subtext {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }
    
    .insight-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .insight-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    
    .insight-content {
        color: #475569;
        line-height: 1.6;
    }
    
    .insight-list {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0 0;
    }
    
    .insight-list li {
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .insight-list li::before {
        content: '•';
        color: #3b82f6;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table thead {
        background: #f8fafc;
    }
    
    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .data-table td {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .data-table tr:hover {
        background: #f8fafc;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-success {
        background: #dcfce7;
        color: #166534;
    }
    
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-danger {
        background: #fecaca;
        color: #991b1b;
    }
    
    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .progress-bar {
        width: 100px;
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
    }
    
    .progress-fill {
        height: 100%;
        transition: width 0.3s;
    }
    
    .progress-fill.success {
        background: #10b981;
    }
    
    .progress-fill.warning {
        background: #f59e0b;
    }
    
    .progress-fill.danger {
        background: #ef4444;
    }
    
    .export-buttons {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    
    @media print {
        .date-filter, .export-buttons, .btn { display: none; }
    }
</style>

<div class="report-container">
    <!-- Header -->
    <div class="report-header">
        <div>
            <h1 class="report-title">⚖️ Production vs Sales</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">
                <?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>
            </p>
        </div>
        
        <a href="<?php echo BASE_URL; ?>Views/reports/index.php" class="btn btn-secondary">← Back to Reports</a>
    </div>
    
    <!-- Date Filter -->
    <form method="GET" class="date-filter">
        <label>From:</label>
        <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
        
        <label>To:</label>
        <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
        
        <button type="submit" class="btn btn-primary">Apply</button>
        
        <div class="quick-filters">
            <button type="button" onclick="setDateRange('today')">Today</button>
            <button type="button" onclick="setDateRange('week')">This Week</button>
            <button type="button" onclick="setDateRange('month')">This Month</button>
            <button type="button" onclick="setDateRange('year')">This Year</button>
        </div>
    </form>
    
    <!-- Export Buttons -->
    <div class="export-buttons">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
        <button onclick="exportCSV()" class="btn btn-success">📊 Export CSV</button>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card primary">
            <div class="summary-label">Units Produced</div>
            <div class="summary-value"><?php echo number_format($totalProduced); ?></div>
            <div class="summary-subtext">In <?php echo count($comparisonData); ?> products</div>
        </div>
        
        <div class="summary-card success">
            <div class="summary-label">Units Sold</div>
            <div class="summary-value"><?php echo number_format($totalSold); ?></div>
            <div class="summary-subtext">Total revenue: $<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        
        <div class="summary-card <?php echo $sellThroughRate >= 80 ? 'success' : ($sellThroughRate >= 50 ? 'warning' : 'danger'); ?>">
            <div class="summary-label">Sell-Through Rate</div>
            <div class="summary-value"><?php echo number_format($sellThroughRate, 1); ?>%</div>
            <div class="summary-subtext">
                <?php if ($sellThroughRate >= 80): ?>
                    Excellent - Strong demand
                <?php elseif ($sellThroughRate >= 50): ?>
                    Good - Manageable inventory
                <?php else: ?>
                    Low - Overproduction risk
                <?php endif; ?>
            </div>
        </div>
        
        <div class="summary-card warning">
            <div class="summary-label">Produced Not Sold</div>
            <div class="summary-value"><?php echo number_format($producedNotSold); ?></div>
            <div class="summary-subtext">In <?php echo $overproducedCount; ?> products</div>
        </div>
        
        <div class="summary-card danger">
            <div class="summary-label">Demand Exceeded Supply</div>
            <div class="summary-value"><?php echo $underproducedCount; ?></div>
            <div class="summary-subtext">Products sold more than produced</div>
        </div>
    </div>
    
    <!-- Key Insights -->
    <div class="insight-card">
        <h3 class="insight-title">📊 Key Insights</h3>
        <div class="insight-content">
            <ul class="insight-list">
                <?php if ($sellThroughRate >= 90): ?>
                    <li><strong>High Demand:</strong> You're selling <?php echo number_format($sellThroughRate, 1); ?>% of production. Consider increasing batch sizes.</li>
                <?php elseif ($sellThroughRate < 50): ?>
                    <li><strong>Inventory Risk:</strong> Only <?php echo number_format($sellThroughRate, 1); ?>% sell-through. Review production forecasts to reduce overproduction.</li>
                <?php endif; ?>
                
                <?php if ($underproducedCount > 0): ?>
                    <li><strong>Stock Shortages:</strong> <?php echo $underproducedCount; ?> products sold more than produced this period (using existing inventory).</li>
                <?php endif; ?>
                
                <?php if ($producedNotSold > 0): ?>
                    <li><strong>Excess Inventory:</strong> <?php echo number_format($producedNotSold); ?> units produced but not sold. Monitor for slow-moving items.</li>
                <?php endif; ?>
                
                <?php if ($overproducedCount > 3): ?>
                    <li><strong>Production Planning:</strong> <?php echo $overproducedCount; ?> products have excess inventory. Consider reducing batch frequencies.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Comparison Table -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Product-by-Product Analysis</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Batches</th>
                    <th>Produced</th>
                    <th>Sold</th>
                    <th>Current Stock</th>
                    <th>Sell-Through %</th>
                    <th>Net Change</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comparisonData)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No production or sales data for this period
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comparisonData as $row): 
                        $sellThrough = $row['units_produced'] > 0 ? ($row['units_sold'] / $row['units_produced']) * 100 : 0;
                        $netChange = $row['units_produced'] - $row['units_sold'];
                        
                        // Determine status
                        if ($row['units_sold'] > $row['units_produced']) {
                            $status = 'Under-Produced';
                            $statusBadge = 'danger';
                        } elseif ($sellThrough >= 80) {
                            $status = 'Balanced';
                            $statusBadge = 'success';
                        } elseif ($sellThrough >= 50) {
                            $status = 'Manageable';
                            $statusBadge = 'warning';
                        } else {
                            $status = 'Over-Produced';
                            $statusBadge = 'danger';
                        }
                        
                        $progressClass = $sellThrough >= 80 ? 'success' : ($sellThrough >= 50 ? 'warning' : 'danger');
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['project_name']); ?></strong>
                            </td>
                            <td><?php echo number_format($row['batches_produced']); ?></td>
                            <td style="font-weight: 600; color: #3b82f6;">
                                <?php echo number_format($row['units_produced']); ?>
                            </td>
                            <td style="font-weight: 600; color: #10b981;">
                                <?php echo number_format($row['units_sold']); ?>
                            </td>
                            <td>
                                <?php echo number_format($row['current_stock']); ?>
                                <?php if ($row['current_stock'] < $row['reorder_point']): ?>
                                    <span style="color: #ef4444; font-size: 0.75rem;">⚠️ Low</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo number_format($sellThrough, 1); ?>%
                                <div class="progress-bar">
                                    <div class="progress-fill <?php echo $progressClass; ?>" 
                                         style="width: <?php echo min($sellThrough, 100); ?>%;"></div>
                                </div>
                            </td>
                            <td style="color: <?php echo $netChange > 0 ? '#f59e0b' : '#10b981'; ?>; font-weight: 600;">
                                <?php echo $netChange > 0 ? '+' : ''; ?><?php echo number_format($netChange); ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $statusBadge; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Date range quick filters
function setDateRange(period) {
    const endDate = new Date();
    let startDate = new Date();
    
    switch(period) {
        case 'today':
            startDate = new Date();
            break;
        case 'week':
            startDate.setDate(endDate.getDate() - 7);
            break;
        case 'month':
            startDate.setMonth(endDate.getMonth() - 1);
            break;
        case 'year':
            startDate.setFullYear(endDate.getFullYear() - 1);
            break;
    }
    
    document.querySelector('input[name="start_date"]').value = formatDate(startDate);
    document.querySelector('input[name="end_date"]').value = formatDate(endDate);
    document.querySelector('form.date-filter').submit();
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// CSV Export
function exportCSV() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    window.location.href = '<?php echo BASE_URL; ?>Views/reports/export_production_vs_sales_csv.php?start_date=' + startDate + '&end_date=' + endDate;
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>
