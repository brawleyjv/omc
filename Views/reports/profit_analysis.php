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

// Get date range from URL or default to last 30 days
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get profit analysis: production costs vs sale prices
$profitQuery = "
    SELECT 
        p.id as project_id,
        p.project_name,
        p.production_status,
        p.cost_per_unit as production_cost,
        
        -- Production data
        COUNT(DISTINCT pb.id) as batch_count,
        SUM(pb.quantity_produced) as units_produced,
        SUM(pb.material_cost + pb.labor_cost) as total_production_cost,
        AVG(pb.material_cost + pb.labor_cost) / NULLIF(AVG(pb.quantity_produced), 0) as actual_cost_per_unit,
        
        -- Sales data (if Etsy connected)
        " . ($etsyConnected ? "
        COALESCE(SUM(oi.quantity), 0) as units_sold,
        COALESCE(SUM(oi.total_price), 0) as total_revenue,
        COALESCE(AVG(oi.unit_price), 0) as avg_sale_price,
        " : "
        0 as units_sold,
        0 as total_revenue,
        0 as avg_sale_price,
        ") . "
        
        -- Inventory
        p.inventory_quantity as current_stock
        
    FROM projects p
    LEFT JOIN production_batches pb ON p.id = pb.project_id 
        AND pb.production_date BETWEEN :start_date AND :end_date
    " . ($etsyConnected ? "
    LEFT JOIN etsy_order_items oi ON p.id = oi.project_id
    LEFT JOIN etsy_orders o ON oi.etsy_order_id = o.id 
        AND o.order_date BETWEEN :start_date AND :end_date
        AND o.status != 'cancelled'
    " : "") . "
    
    WHERE p.production_status IN ('ready', 'active')
    GROUP BY p.id, p.project_name, p.production_status, p.cost_per_unit, p.inventory_quantity
    HAVING batch_count > 0 " . ($etsyConnected ? "OR units_sold > 0" : "") . "
    ORDER BY total_revenue DESC, units_produced DESC
";

$profitStmt = $pdo->prepare($profitQuery);
$profitStmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$profitData = $profitStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate summary statistics
$totalProfit = 0;
$totalRevenue = 0;
$totalCost = 0;
$profitableCount = 0;

foreach ($profitData as &$row) {
    // Calculate profit metrics
    $revenue = $row['total_revenue'];
    $cost = $row['total_production_cost'];
    $profit = $revenue - $cost;
    $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
    
    $row['profit'] = $profit;
    $row['profit_margin'] = $profitMargin;
    $row['profit_per_unit'] = $row['units_sold'] > 0 ? $profit / $row['units_sold'] : 0;
    
    $totalRevenue += $revenue;
    $totalCost += $cost;
    $totalProfit += $profit;
    
    if ($profit > 0) $profitableCount++;
}

$overallMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

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
    .summary-card.danger { border-left: 4px solid #ef4444; }
    .summary-card.warning { border-left: 4px solid #f59e0b; }
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
    
    .alert {
        background: #dbeafe;
        border: 1px solid #3b82f6;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .alert.warning {
        background: #fef3c7;
        border-color: #fbbf24;
    }
    
    .alert-content {
        color: #1e40af;
    }
    
    .alert.warning .alert-content {
        color: #78350f;
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
    
    .profit-positive {
        color: #059669;
        font-weight: 600;
    }
    
    .profit-negative {
        color: #dc2626;
        font-weight: 600;
    }
    
    .margin-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .margin-excellent {
        background: #dcfce7;
        color: #166534;
    }
    
    .margin-good {
        background: #d1fae5;
        color: #047857;
    }
    
    .margin-fair {
        background: #fef3c7;
        color: #92400e;
    }
    
    .margin-poor {
        background: #fecaca;
        color: #991b1b;
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
            <h1 class="report-title">📈 Profit Analysis</h1>
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
    
    <?php if (!$etsyConnected): ?>
        <div class="alert warning">
            <div class="alert-content">
                <strong>⚠️ Limited Data:</strong> Etsy is not connected. Profit calculations are based on production costs only. 
                Connect Etsy to see actual revenue and profit margins.
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card success">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">$<?php echo number_format($totalRevenue, 2); ?></div>
            <div class="summary-subtext">From <?php echo count($profitData); ?> products</div>
        </div>
        
        <div class="summary-card danger">
            <div class="summary-label">Total Costs</div>
            <div class="summary-value">$<?php echo number_format($totalCost, 2); ?></div>
            <div class="summary-subtext">Production expenses</div>
        </div>
        
        <div class="summary-card <?php echo $totalProfit >= 0 ? 'success' : 'danger'; ?>">
            <div class="summary-label">Net Profit</div>
            <div class="summary-value">$<?php echo number_format($totalProfit, 2); ?></div>
            <div class="summary-subtext">
                <?php echo $totalProfit >= 0 ? '✓' : '✗'; ?> 
                <?php echo number_format(abs($overallMargin), 1); ?>% margin
            </div>
        </div>
        
        <div class="summary-card primary">
            <div class="summary-label">Profitable Products</div>
            <div class="summary-value"><?php echo $profitableCount; ?> / <?php echo count($profitData); ?></div>
            <div class="summary-subtext">
                <?php echo count($profitData) > 0 ? number_format(($profitableCount / count($profitData)) * 100, 1) : 0; ?>% success rate
            </div>
        </div>
    </div>
    
    <!-- Profit Analysis Table -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">Product Profitability Details</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Units Produced</th>
                    <th>Units Sold</th>
                    <th>Current Stock</th>
                    <th>Production Cost</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                    <th>Margin %</th>
                    <th>Profit/Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($profitData)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No production or sales data for this period
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($profitData as $row): 
                        $marginClass = '';
                        if ($row['profit_margin'] >= 40) $marginClass = 'margin-excellent';
                        elseif ($row['profit_margin'] >= 25) $marginClass = 'margin-good';
                        elseif ($row['profit_margin'] >= 10) $marginClass = 'margin-fair';
                        else $marginClass = 'margin-poor';
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['project_name']); ?></strong>
                                <br><small style="color: #94a3b8;"><?php echo $row['batch_count']; ?> batches</small>
                            </td>
                            <td><?php echo number_format($row['units_produced']); ?></td>
                            <td><?php echo number_format($row['units_sold']); ?></td>
                            <td><?php echo number_format($row['current_stock']); ?></td>
                            <td>$<?php echo number_format($row['total_production_cost'], 2); ?></td>
                            <td style="font-weight: 600; color: #10b981;">
                                $<?php echo number_format($row['total_revenue'], 2); ?>
                            </td>
                            <td class="<?php echo $row['profit'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                <?php echo $row['profit'] >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($row['profit']), 2); ?>
                            </td>
                            <td>
                                <span class="margin-badge <?php echo $marginClass; ?>">
                                    <?php echo number_format($row['profit_margin'], 1); ?>%
                                </span>
                            </td>
                            <td class="<?php echo $row['profit_per_unit'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                <?php if ($row['units_sold'] > 0): ?>
                                    <?php echo $row['profit_per_unit'] >= 0 ? '+' : '-'; ?>$<?php echo number_format(abs($row['profit_per_unit']), 2); ?>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">N/A</span>
                                <?php endif; ?>
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
    window.location.href = '<?php echo BASE_URL; ?>Views/reports/export_profit_analysis_csv.php?start_date=' + startDate + '&end_date=' + endDate;
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>
