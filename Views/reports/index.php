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

// Get quick stats for the dashboard
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM production_batches WHERE production_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as batches_30_days,
        (SELECT COUNT(DISTINCT project_id) FROM production_batches) as projects_produced,
        (SELECT COUNT(*) FROM projects WHERE production_status IN ('ready', 'active')) as active_products,
        (SELECT COUNT(*) FROM projects WHERE inventory_quantity < reorder_point AND production_status = 'active') as low_stock_count,
        (SELECT 
            SUM(p.inventory_quantity * COALESCE(e.total_estimate, p.cost_per_unit, 0))
            FROM projects p
            LEFT JOIN estimates e ON p.estimate_id = e.id
            WHERE p.production_status IN ('ready', 'active')
        ) as inventory_value
";
$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get Etsy stats if connected
$etsyStats = null;
if ($etsyConnected) {
    $etsyStatsQuery = "
        SELECT 
            COUNT(*) as total_orders,
            SUM(grand_total) as total_revenue,
            COUNT(DISTINCT DATE(order_date)) as days_with_orders,
            (SELECT COUNT(*) FROM etsy_order_items) as items_sold
        FROM etsy_orders 
        WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ";
    $etsyStatsStmt = $pdo->query($etsyStatsQuery);
    $etsyStats = $etsyStatsStmt->fetch(PDO::FETCH_ASSOC);
}

// Get recent production batches (last 10)
$recentBatchesQuery = "
    SELECT 
        pb.production_date,
        pb.batch_number,
        p.project_name,
        pb.quantity_produced,
        pb.labor_hours,
        pb.laser_time,
        pb.mill_time,
        pb.material_cost + pb.labor_cost as total_cost,
        pb.produced_by
    FROM production_batches pb
    INNER JOIN projects p ON pb.project_id = p.id
    ORDER BY pb.production_date DESC, pb.batch_number DESC
    LIMIT 10
";
$recentBatchesStmt = $pdo->query($recentBatchesQuery);
$recentBatches = $recentBatchesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get inventory status (active products)
$inventoryQuery = "
    SELECT 
        p.project_name,
        p.production_status,
        p.inventory_quantity,
        p.reorder_point,
        p.batch_size,
        COALESCE(e.total_estimate, p.cost_per_unit, 0) as unit_value,
        p.inventory_quantity * COALESCE(e.total_estimate, p.cost_per_unit, 0) as inventory_value,
        COUNT(pb.id) as batch_count,
        SUM(pb.quantity_produced) as total_produced,
        CASE 
            WHEN p.inventory_quantity < p.reorder_point THEN 'Low Stock'
            WHEN p.inventory_quantity = 0 THEN 'Out of Stock'
            ELSE 'OK'
        END as stock_status
    FROM projects p
    LEFT JOIN estimates e ON p.estimate_id = e.id
    LEFT JOIN production_batches pb ON p.id = pb.project_id
    WHERE p.production_status IN ('ready', 'active')
    GROUP BY p.id, p.project_name, p.production_status, p.inventory_quantity, p.reorder_point, p.batch_size, e.total_estimate, p.cost_per_unit
    ORDER BY p.production_status DESC, p.project_name ASC
";
$inventoryStmt = $pdo->query($inventoryQuery);
$inventoryData = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent Etsy orders if connected
$recentOrders = [];
if ($etsyConnected) {
    $recentOrdersQuery = "
        SELECT 
            order_date,
            buyer_name,
            items_count,
            grand_total,
            status
        FROM etsy_orders
        ORDER BY order_date DESC
        LIMIT 10
    ";
    $recentOrdersStmt = $pdo->query($recentOrdersQuery);
    $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get low stock alerts
$lowStockQuery = "
    SELECT 
        p.project_name,
        p.inventory_quantity,
        p.reorder_point,
        p.batch_size,
        p.reorder_point - p.inventory_quantity as shortage
    FROM projects p
    WHERE p.production_status = 'active'
    AND p.inventory_quantity < p.reorder_point
    ORDER BY shortage DESC
";
$lowStockStmt = $pdo->query($lowStockQuery);
$lowStockItems = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../header.php';
?>

<style>
    .reports-hub {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .hub-header {
        margin-bottom: 2rem;
    }
    
    .hub-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .hub-subtitle {
        color: #64748b;
        font-size: 1rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 3rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #3b82f6;
    }
    
    .stat-card.success { border-left-color: #10b981; }
    .stat-card.warning { border-left-color: #f59e0b; }
    .stat-card.info { border-left-color: #06b6d4; }
    .stat-card.purple { border-left-color: #8b5cf6; }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .reports-section {
        margin-bottom: 3rem;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .section-icon {
        font-size: 1.5rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    
    .report-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    
    .report-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    
    .report-card-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .report-card.production .report-card-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    }
    
    .report-card.inventory .report-card-header {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }
    
    .report-card.etsy .report-card-header {
        background: linear-gradient(135deg, #f56400 0%, #d94f00 100%);
    }
    
    .report-card.financial .report-card-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .report-card.combined .report-card-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .report-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    
    .report-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .report-card-body {
        padding: 1.5rem;
    }
    
    .report-description {
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }
    
    .report-features {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .report-features li {
        font-size: 0.875rem;
        color: #475569;
        padding: 0.25rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .report-features li::before {
        content: '✓';
        color: #10b981;
        font-weight: 700;
    }
    
    .disabled-notice {
        background: #fef3c7;
        border: 1px solid #fbbf24;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: start;
        gap: 0.75rem;
    }
    
    .disabled-notice-icon {
        font-size: 1.5rem;
    }
    
    .disabled-notice-content h4 {
        margin: 0 0 0.5rem 0;
        color: #92400e;
        font-size: 1rem;
    }
    
    .disabled-notice-content p {
        margin: 0;
        color: #78350f;
        font-size: 0.875rem;
    }
    
    .btn-connect {
        display: inline-block;
        margin-top: 0.75rem;
        padding: 0.5rem 1rem;
        background: #f59e0b;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-connect:hover {
        background: #d97706;
        color: white;
    }
</style>

<div class="reports-hub">
    <!-- Header -->
    <div class="hub-header">
        <h1 class="hub-title">📊 Reports Hub</h1>
        <p class="hub-subtitle">Comprehensive reporting and analytics for your woodworking business</p>
    </div>
    
    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-value"><?php echo number_format($stats['batches_30_days']); ?></div>
            <div class="stat-label">Production Batches (30 days)</div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-value"><?php echo number_format($stats['active_products']); ?></div>
            <div class="stat-label">Active Products</div>
        </div>
        
        <div class="stat-card <?php echo $stats['low_stock_count'] > 0 ? 'warning' : 'success'; ?>">
            <div class="stat-value"><?php echo number_format($stats['low_stock_count']); ?></div>
            <div class="stat-label">Low Stock Alerts</div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-value">$<?php echo number_format($stats['inventory_value'] ?? 0, 2); ?></div>
            <div class="stat-label">Inventory Value</div>
        </div>
        
        <?php if ($etsyConnected && $etsyStats): ?>
            <div class="stat-card info">
                <div class="stat-value"><?php echo number_format($etsyStats['total_orders']); ?></div>
                <div class="stat-label">Etsy Orders (30 days)</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-value">$<?php echo number_format($etsyStats['total_revenue'], 2); ?></div>
                <div class="stat-label">Etsy Revenue (30 days)</div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Production Reports -->
    <div class="reports-section">
        <div class="section-header">
            <span class="section-icon">🏭</span>
            <h2 class="section-title">Production Reports</h2>
        </div>
        
        <div class="reports-grid">
            <a href="<?php echo BASE_URL; ?>Views/production/print_production_report.php" class="report-card production">
                <div class="report-card-header">
                    <div class="report-icon">📋</div>
                    <div class="report-title">Production Batch History</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        View detailed production batch records with time tracking, costs, and labor hours.
                    </p>
                    <ul class="report-features">
                        <li>Date range filtering</li>
                        <li>Time per unit calculations</li>
                        <li>PDF export for printing</li>
                        <li>CSV export for Excel</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/reports/efficiency_metrics.php" class="report-card production">
                <div class="report-card-header">
                    <div class="report-icon">⚡</div>
                    <div class="report-title">Efficiency Metrics</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Track production efficiency trends, identify improvements, and optimize workflow.
                    </p>
                    <ul class="report-features">
                        <li>Time per piece trends</li>
                        <li>Best/worst performers</li>
                        <li>Efficiency charts</li>
                        <li>Improvement tracking</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/reports/batch_history.php" class="report-card production">
                <div class="report-card-header">
                    <div class="report-icon">📈</div>
                    <div class="report-title">Project Batch History</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        View all batches for specific projects with performance trends over time.
                    </p>
                    <ul class="report-features">
                        <li>Project-specific batches</li>
                        <li>Time trend analysis</li>
                        <li>Cost tracking</li>
                        <li>Performance graphs</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Inventory Reports -->
    <div class="reports-section">
        <div class="section-header">
            <span class="section-icon">📦</span>
            <h2 class="section-title">Inventory Reports</h2>
        </div>
        
        <div class="reports-grid">
            <a href="<?php echo BASE_URL; ?>Views/production/print_inventory_report.php" class="report-card inventory">
                <div class="report-card-header">
                    <div class="report-icon">📊</div>
                    <div class="report-title">Current Inventory Status</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Complete inventory snapshot with stock levels, values, and low stock alerts.
                    </p>
                    <ul class="report-features">
                        <li>Real-time stock levels</li>
                        <li>Low stock highlighting</li>
                        <li>Inventory value totals</li>
                        <li>PDF & CSV export</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/production/inventory_dashboard.php" class="report-card inventory">
                <div class="report-card-header">
                    <div class="report-icon">🔔</div>
                    <div class="report-title">Low Stock Alerts</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Monitor products below reorder points and manage inventory adjustments.
                    </p>
                    <ul class="report-features">
                        <li>Interactive dashboard</li>
                        <li>Manual adjustments</li>
                        <li>Transaction history</li>
                        <li>Reorder recommendations</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Etsy Reports -->
    <div class="reports-section">
        <div class="section-header">
            <span class="section-icon">🛒</span>
            <h2 class="section-title">Etsy Sales Reports</h2>
        </div>
        
        <?php if (!$etsyConnected): ?>
            <div class="disabled-notice">
                <div class="disabled-notice-icon">⚠️</div>
                <div class="disabled-notice-content">
                    <h4>Etsy Not Connected</h4>
                    <p>Connect your Etsy shop to access sales reports, product performance, and revenue analytics.</p>
                    <a href="<?php echo BASE_URL; ?>Views/settings.php#etsy" class="btn-connect">Connect Etsy Shop</a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="reports-grid">
            <a href="<?php echo BASE_URL; ?>Views/reports/etsy_sales.php" class="report-card etsy" <?php echo !$etsyConnected ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                <div class="report-card-header">
                    <div class="report-icon">💰</div>
                    <div class="report-title">Sales Analytics</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Revenue trends, order statistics, and sales performance over time.
                    </p>
                    <ul class="report-features">
                        <li>Daily/weekly/monthly revenue</li>
                        <li>Order volume trends</li>
                        <li>Average order value</li>
                        <li>Revenue charts</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/reports/product_performance.php" class="report-card etsy" <?php echo !$etsyConnected ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                <div class="report-card-header">
                    <div class="report-icon">🏆</div>
                    <div class="report-title">Product Performance</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Identify best sellers, sales velocity, and product trends.
                    </p>
                    <ul class="report-features">
                        <li>Top selling products</li>
                        <li>Sales velocity</li>
                        <li>Product rankings</li>
                        <li>Performance charts</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/reports/customer_insights.php" class="report-card etsy" <?php echo !$etsyConnected ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                <div class="report-card-header">
                    <div class="report-icon">👥</div>
                    <div class="report-title">Customer Insights</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Analyze customer behavior, repeat purchases, and geographic distribution.
                    </p>
                    <ul class="report-features">
                        <li>Repeat customer rate</li>
                        <li>Geographic distribution</li>
                        <li>Customer lifetime value</li>
                        <li>Purchase patterns</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Financial Reports -->
    <div class="reports-section">
        <div class="section-header">
            <span class="section-icon">💵</span>
            <h2 class="section-title">Financial Reports</h2>
        </div>
        
        <div class="reports-grid">
            <a href="<?php echo BASE_URL; ?>Views/reports/profit_analysis.php" class="report-card financial">
                <div class="report-card-header">
                    <div class="report-icon">📈</div>
                    <div class="report-title">Profit Analysis</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Compare actual production costs to sale prices and calculate profit margins.
                    </p>
                    <ul class="report-features">
                        <li>Profit margins by product</li>
                        <li>Cost vs revenue</li>
                        <li>Profitability trends</li>
                        <li>Margin analysis</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/reports/cost_analysis.php" class="report-card financial">
                <div class="report-card-header">
                    <div class="report-icon">💡</div>
                    <div class="report-title">Cost Analysis</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Compare estimated vs actual costs to improve future project estimates.
                    </p>
                    <ul class="report-features">
                        <li>Estimate vs actual</li>
                        <li>Cost variance tracking</li>
                        <li>Budget accuracy</li>
                        <li>Improvement insights</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Combined Reports -->
    <div class="reports-section">
        <div class="section-header">
            <span class="section-icon">🔗</span>
            <h2 class="section-title">Combined Analysis</h2>
        </div>
        
        <div class="reports-grid">
            <a href="<?php echo BASE_URL; ?>Views/reports/production_vs_sales.php" class="report-card combined" <?php echo !$etsyConnected ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                <div class="report-card-header">
                    <div class="report-icon">⚖️</div>
                    <div class="report-title">Production vs Sales</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Compare what you produced to what sold, identify gaps and opportunities.
                    </p>
                    <ul class="report-features">
                        <li>Production/sales comparison</li>
                        <li>Inventory turnover</li>
                        <li>Demand forecasting</li>
                        <li>Gap analysis</li>
                    </ul>
                </div>
            </a>
            
            <a href="<?php echo BASE_URL; ?>Views/reports/inventory_forecasting.php" class="report-card combined" <?php echo !$etsyConnected ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
                <div class="report-card-header">
                    <div class="report-icon">🔮</div>
                    <div class="report-title">Inventory Forecasting</div>
                </div>
                <div class="report-card-body">
                    <p class="report-description">
                        Predict future inventory needs based on sales velocity and trends.
                    </p>
                    <ul class="report-features">
                        <li>Sales velocity tracking</li>
                        <li>Days of inventory remaining</li>
                        <li>Reorder predictions</li>
                        <li>Production planning</li>
                    </ul>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
