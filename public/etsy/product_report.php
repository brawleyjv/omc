<?php
/**
 * Etsy Product Sales Report
 * 
 * View sales statistics for products sold on Etsy
 * - Total sales per product
 * - Revenue analytics
 * - Link status
 * - Date range filtering
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Database.php';

use MyApp\Models\Database;

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

// Get filter parameters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today
$linkStatus = $_GET['link_status'] ?? 'all'; // all, linked, unlinked

// Build query with filters
$whereConditions = ["DATE(eoi.created_at) BETWEEN :date_from AND :date_to"];
$params = [
    ':date_from' => $dateFrom,
    ':date_to' => $dateTo
];

if ($linkStatus === 'linked') {
    $whereConditions[] = "eoi.project_id IS NOT NULL";
} elseif ($linkStatus === 'unlinked') {
    $whereConditions[] = "eoi.project_id IS NULL";
}

$whereClause = implode(' AND ', $whereConditions);

// Get product sales data
$query = "SELECT 
            eoi.product_name,
            eoi.product_sku,
            eoi.project_id,
            p.project_name,
            COUNT(DISTINCT eoi.etsy_order_id) as num_orders,
            SUM(eoi.quantity) as total_quantity,
            SUM(eoi.total_price) as total_revenue,
            AVG(eoi.unit_price) as avg_price,
            MIN(eoi.created_at) as first_sale,
            MAX(eoi.created_at) as last_sale,
            CASE WHEN eoi.project_id IS NOT NULL THEN 'Linked' ELSE 'Unlinked' END as link_status
          FROM etsy_order_items eoi
          LEFT JOIN projects p ON eoi.project_id = p.id
          WHERE $whereClause
          GROUP BY eoi.product_name, eoi.product_sku, eoi.project_id, p.project_name
          ORDER BY total_revenue DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$totalOrders = array_sum(array_column($products, 'num_orders'));
$totalQuantity = array_sum(array_column($products, 'total_quantity'));
$totalRevenue = array_sum(array_column($products, 'total_revenue'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etsy Product Sales Report - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .report-filters {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        .stat-card-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        .product-row.unlinked {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>📊 Product Sales Report</h1>
                    <p>Analyze Etsy product performance and revenue</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>public/etsy/dashboard.php" class="nav-link">Etsy Dashboard</a>
                <a href="<?php echo BASE_URL; ?>public/etsy/link_products.php" class="nav-link">Link Products</a>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Main Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <!-- Filters -->
        <div class="report-filters">
            <form method="GET" class="form-row">
                <div class="form-group">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="<?php echo htmlspecialchars($dateFrom); ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" 
                           value="<?php echo htmlspecialchars($dateTo); ?>">
                </div>
                
                <div class="form-group">
                    <label for="link_status" class="form-label">Link Status</label>
                    <select id="link_status" name="link_status" class="form-control">
                        <option value="all" <?php echo $linkStatus === 'all' ? 'selected' : ''; ?>>All Products</option>
                        <option value="linked" <?php echo $linkStatus === 'linked' ? 'selected' : ''; ?>>Linked Only</option>
                        <option value="unlinked" <?php echo $linkStatus === 'unlinked' ? 'selected' : ''; ?>>Unlinked Only</option>
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon">🔍</span> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-card-value"><?php echo count($products); ?></div>
                <div class="stat-card-label">Unique Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-value"><?php echo $totalOrders; ?></div>
                <div class="stat-card-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-value"><?php echo $totalQuantity; ?></div>
                <div class="stat-card-label">Items Sold</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-card-label">Total Revenue</div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Product Sales Details</h2>
                <p class="card-subtitle">
                    Showing results from <?php echo date('M j, Y', strtotime($dateFrom)); ?> 
                    to <?php echo date('M j, Y', strtotime($dateTo)); ?>
                </p>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        No products found for the selected filters.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Linked Project</th>
                                <th>Orders</th>
                                <th>Quantity</th>
                                <th>Avg Price</th>
                                <th>Total Revenue</th>
                                <th>First Sale</th>
                                <th>Last Sale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="product-row <?php echo $product['link_status'] === 'Unlinked' ? 'unlinked' : ''; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                        <?php if ($product['link_status'] === 'Unlinked'): ?>
                                            <br><small style="color: #dc2626;">⚠️ Not linked</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['product_sku'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($product['project_name']): ?>
                                            <span class="badge badge-success">
                                                <?php echo htmlspecialchars($product['project_name']); ?>
                                            </span>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>public/etsy/link_products.php" class="btn btn-sm btn-secondary">
                                                Link Now
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['num_orders']; ?></td>
                                    <td><?php echo $product['total_quantity']; ?></td>
                                    <td>$<?php echo number_format($product['avg_price'], 2); ?></td>
                                    <td><strong>$<?php echo number_format($product['total_revenue'], 2); ?></strong></td>
                                    <td><?php echo date('M j, Y', strtotime($product['first_sale'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($product['last_sale'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f9fafb; font-weight: 600;">
                                <td colspan="3">TOTALS</td>
                                <td><?php echo $totalOrders; ?></td>
                                <td><?php echo $totalQuantity; ?></td>
                                <td>-</td>
                                <td><strong>$<?php echo number_format($totalRevenue, 2); ?></strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
