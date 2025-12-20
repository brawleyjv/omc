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

// Check if Etsy is connected
if (!$etsyModel->isConnected()) {
    header("Location: " . BASE_URL . "Views/reports/index.php");
    exit();
}

// Get date range from URL or default to last 30 days
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get sales summary
$summaryQuery = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(grand_total) as total_revenue,
        AVG(grand_total) as avg_order_value,
        SUM(items_count) as total_items,
        COUNT(DISTINCT buyer_user_id) as unique_customers
    FROM etsy_orders
    WHERE order_date BETWEEN :start_date AND :end_date
    AND status != 'cancelled'
";
$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

// Get daily revenue for chart
$dailyQuery = "
    SELECT 
        DATE(order_date) as date,
        COUNT(*) as orders,
        SUM(grand_total) as revenue
    FROM etsy_orders
    WHERE order_date BETWEEN :start_date AND :end_date
    AND status != 'cancelled'
    GROUP BY DATE(order_date)
    ORDER BY date ASC
";
$dailyStmt = $pdo->prepare($dailyQuery);
$dailyStmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

// Get top selling products
$topProductsQuery = "
    SELECT 
        oi.product_name,
        oi.etsy_listing_id,
        SUM(oi.quantity) as total_sold,
        SUM(oi.total_price) as revenue,
        COUNT(DISTINCT oi.etsy_order_id) as order_count,
        AVG(oi.unit_price) as avg_price
    FROM etsy_order_items oi
    INNER JOIN etsy_orders o ON oi.etsy_order_id = o.id
    WHERE o.order_date BETWEEN :start_date AND :end_date
    AND o.status != 'cancelled'
    GROUP BY oi.product_name, oi.etsy_listing_id
    ORDER BY total_sold DESC
    LIMIT 10
";
$topProductsStmt = $pdo->prepare($topProductsQuery);
$topProductsStmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get order status breakdown
$statusQuery = "
    SELECT 
        status,
        COUNT(*) as count,
        SUM(grand_total) as revenue
    FROM etsy_orders
    WHERE order_date BETWEEN :start_date AND :end_date
    GROUP BY status
    ORDER BY count DESC
";
$statusStmt = $pdo->prepare($statusQuery);
$statusStmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
$statusData = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    .chart-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .chart-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    
    #revenueChart {
        width: 100%;
        height: 300px;
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
            <h1 class="report-title">💰 Etsy Sales Analytics</h1>
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
        <div class="summary-card success">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">$<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></div>
        </div>
        
        <div class="summary-card primary">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value"><?php echo number_format($summary['total_orders'] ?? 0); ?></div>
        </div>
        
        <div class="summary-card warning">
            <div class="summary-label">Average Order Value</div>
            <div class="summary-value">$<?php echo number_format($summary['avg_order_value'] ?? 0, 2); ?></div>
        </div>
        
        <div class="summary-card info">
            <div class="summary-label">Items Sold</div>
            <div class="summary-value"><?php echo number_format($summary['total_items'] ?? 0); ?></div>
        </div>
        
        <div class="summary-card info">
            <div class="summary-label">Unique Customers</div>
            <div class="summary-value"><?php echo number_format($summary['unique_customers'] ?? 0); ?></div>
        </div>
    </div>
    
    <!-- Revenue Chart -->
    <div class="chart-card">
        <h3 class="chart-title">Daily Revenue Trend</h3>
        <canvas id="revenueChart"></canvas>
    </div>
    
    <!-- Top Products Table -->
    <div class="table-container" style="margin-bottom: 2rem;">
        <div class="table-header">
            <h3 class="table-title">🏆 Top Selling Products</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product Name</th>
                    <th>Units Sold</th>
                    <th>Revenue</th>
                    <th>Orders</th>
                    <th>Avg Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topProducts)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No sales data for this period
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topProducts as $index => $product): ?>
                        <tr>
                            <td style="font-weight: 600;">#<?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo number_format($product['total_sold']); ?></td>
                            <td>$<?php echo number_format($product['revenue'], 2); ?></td>
                            <td><?php echo number_format($product['order_count']); ?></td>
                            <td>$<?php echo number_format($product['avg_price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Order Status Breakdown -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">📦 Order Status Breakdown</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($statusData)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No status data available
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $totalOrders = array_sum(array_column($statusData, 'count'));
                    foreach ($statusData as $status): 
                        $percentage = ($status['count'] / $totalOrders) * 100;
                    ?>
                        <tr>
                            <td style="text-transform: capitalize; font-weight: 500;">
                                <?php echo htmlspecialchars($status['status']); ?>
                            </td>
                            <td><?php echo number_format($status['count']); ?></td>
                            <td>$<?php echo number_format($status['revenue'], 2); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Daily Revenue Chart
const dailyData = <?php echo json_encode($dailyData); ?>;
const ctx = document.getElementById('revenueChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: dailyData.map(d => d.date),
        datasets: [{
            label: 'Revenue',
            data: dailyData.map(d => parseFloat(d.revenue)),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Orders',
            data: dailyData.map(d => parseInt(d.orders)),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue ($)'
                },
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(2);
                    }
                }
            },
            y1: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Orders'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

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
    window.location.href = '<?php echo BASE_URL; ?>Views/reports/export_etsy_sales_csv.php?start_date=' + startDate + '&end_date=' + endDate;
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>
