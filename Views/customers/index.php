<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <?php require_once BASE_PATH . '/Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Customer Management</h1>
                <p class="text-muted">Manage your customers and client relationships</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add Customer Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">➕</span>
                    </div>
                    <h3 class="card-title">Add New Customer</h3>
                    <p class="text-muted mb-4">Add a new customer to your database</p>
                    <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="btn btn-primary w-full">
                        <span class="icon">👤</span>
                        Add Customer
                    </a>
                </div>
            </div>

            <!-- List Customers Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📋</span>
                    </div>
                    <h3 class="card-title">View All Customers</h3>
                    <p class="text-muted mb-4">Browse and manage all customers in your system</p>
                    <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="btn btn-primary w-full">
                        <span class="icon">👁️</span>
                        View Customers
                    </a>
                </div>
            </div>

            <!-- Search Customers Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🔍</span>
                    </div>
                    <h3 class="card-title">Search Customers</h3>
                    <p class="text-muted mb-4">Find specific customers by name, email, or phone</p>
                    <a href="<?php echo BASE_URL; ?>Views/customers/search_customer.php" class="btn btn-primary w-full">
                        <span class="icon">🔍</span>
                        Search Customers
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-6">
            <div class="card-header">
                <h2>Customer Overview</h2>
                <p class="text-muted">Quick statistics about your customer database</p>
            </div>
            <div class="card-body">
                <?php
                try {
                    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $conn = $database->getConnection();
                    
                    // Get customer count
                    $stmt = $conn->query("SELECT COUNT(*) as customer_count FROM customers");
                    $customer_count = $stmt->fetch(PDO::FETCH_ASSOC)['customer_count'];
                    
                    // Get recent customers
                    $stmt = $conn->query("SELECT first_name, last_name, created_at FROM customers ORDER BY created_at DESC LIMIT 5");
                    $recent_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Get customer projects count
                    $stmt = $conn->query("SELECT COUNT(DISTINCT customer_name) as active_customers FROM projects");
                    $active_customers = $stmt->fetch(PDO::FETCH_ASSOC)['active_customers'];
                    
                } catch (Exception $e) {
                    $customer_count = 0;
                    $recent_customers = [];
                    $active_customers = 0;
                }
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $customer_count; ?></div>
                            <div class="stats-label">Total Customers</div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $active_customers; ?></div>
                            <div class="stats-label">Active Customers</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Recent Customers</h4>
                        <?php if (!empty($recent_customers)): ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($recent_customers, 0, 3) as $customer): ?>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                                        <span class="text-xs text-muted">
                                            <?php 
                                            if (!empty($customer['created_at'])) {
                                                echo date('M j', strtotime($customer['created_at']));
                                            } else {
                                                echo 'Recently';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">No customers added yet.</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="text-primary text-sm">View all customers →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
