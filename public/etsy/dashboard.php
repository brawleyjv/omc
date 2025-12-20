<?php
/**
 * Etsy Dashboard - View Etsy orders and sync status
 * 
 * This page will show synced Etsy orders, sync history, and order management
 * (Will be fully implemented in Phase 2)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/EtsyModel.php';

use MyApp\Models\Database;
use MyApp\Models\EtsyModel;

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
$etsyModel = new EtsyModel($db);

// Check if connected
if (!$etsyModel->isConnected()) {
    $_SESSION['error_message'] = 'You must connect to Etsy first.';
    header('Location: ' . BASE_URL . 'Views/settings.php');
    exit;
}

// Get shop info
$query = "SELECT etsy_shop_name, etsy_shop_id, etsy_last_sync FROM settings WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$shopName = $settings['etsy_shop_name'] ?? 'Unknown';
$shopId = $settings['etsy_shop_id'] ?? '';
$lastSync = $settings['etsy_last_sync'] ?? null;

// Get orders from database
$ordersQuery = "SELECT * FROM etsy_orders ORDER BY created_at DESC LIMIT 50";
$ordersStmt = $db->prepare($ordersQuery);
$ordersStmt->execute();
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent sync logs
$logsQuery = "SELECT * FROM etsy_sync_log ORDER BY completed_at DESC LIMIT 10";
$logsStmt = $db->prepare($logsQuery);
$logsStmt->execute();
$syncLogs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etsy Dashboard - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>🛒 Etsy Dashboard</h1>
                    <p>Manage Etsy orders and sync settings</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>public/etsy/link_products.php" class="nav-link">Link Products</a>
                <a href="<?php echo BASE_URL; ?>public/etsy/product_report.php" class="nav-link">Product Report</a>
                <a href="<?php echo BASE_URL; ?>Views/settings.php" class="nav-link">Settings</a>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <!-- Connection Status -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Connection Status</h2>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin-bottom: 0.5rem;">
                            <strong>Shop:</strong> <?php echo htmlspecialchars($shopName); ?>
                            <?php if ($shopId): ?>
                                <small style="color: #666;">(ID: <?php echo htmlspecialchars($shopId); ?>)</small>
                            <?php endif; ?>
                        </p>
                        <?php if ($lastSync): ?>
                            <p style="margin-bottom: 0; color: #666;">
                                <strong>Last Sync:</strong> <?php echo date('F j, Y g:i A', strtotime($lastSync)); ?>
                            </p>
                        <?php else: ?>
                            <p style="margin-bottom: 0; color: #666;">
                                <strong>Last Sync:</strong> Never
                            </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?php echo BASE_URL; ?>public/etsy/sync_orders.php" class="btn btn-primary">
                            <span class="icon">🔄</span> Sync Orders Now
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Recent Orders</h2>
                <p class="card-subtitle">Showing most recent 50 orders from Etsy</p>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">
                        <strong>No orders found.</strong><br>
                        Click "Sync Orders Now" to import your Etsy orders.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['etsy_order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo $order['items_count']; ?></td>
                                    <td>$<?php echo number_format($order['order_total'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $order['fulfillment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo htmlspecialchars($order['fulfillment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>public/etsy/view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sync History -->
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Sync History</h2>
                <p class="card-subtitle">Recent synchronization operations</p>
            </div>
            <div class="card-body">
                <?php if (empty($syncLogs)): ?>
                    <p style="color: #666;">No sync history available.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Processed</th>
                                <th>Added</th>
                                <th>Updated</th>
                                <th>Failed</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($syncLogs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['sync_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $log['status'] === 'success' ? 'success' : ($log['status'] === 'failure' ? 'error' : 'warning'); ?>">
                                            <?php echo htmlspecialchars($log['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['records_processed']; ?></td>
                                    <td><?php echo $log['records_added']; ?></td>
                                    <td><?php echo $log['records_updated']; ?></td>
                                    <td><?php echo $log['records_failed']; ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($log['completed_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
