<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/VendorController.php';
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Management - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Vendor Management</h1>
                <p class="text-muted">Manage your vendors, suppliers, and business partners</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add Vendor Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">➕</span>
                    </div>
                    <h3 class="card-title">Add New Vendor</h3>
                    <p class="text-muted mb-4">Add a new vendor or supplier to your database</p>
                    <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary w-full">
                        <span class="icon">✏️</span>
                        Add Vendor
                    </a>
                </div>
            </div>

            <!-- List Vendors Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📋</span>
                    </div>
                    <h3 class="card-title">View All Vendors</h3>
                    <p class="text-muted mb-4">Browse and manage all vendors in your system</p>
                    <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="btn btn-primary w-full">
                        <span class="icon">👁️</span>
                        View Vendors
                    </a>
                </div>
            </div>

            <!-- Search Vendors Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🔍</span>
                    </div>
                    <h3 class="card-title">Search Vendors</h3>
                    <p class="text-muted mb-4">Find specific vendors by name or other criteria</p>
                    <a href="<?php echo BASE_URL; ?>Views/vendors/search_vendors.php" class="btn btn-primary w-full">
                        <span class="icon">🔍</span>
                        Search Vendors
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-6">
            <div class="card-header">
                <h2>Vendor Overview</h2>
                <p class="text-muted">Quick statistics about your vendor database</p>
            </div>
            <div class="card-body">
                <?php
                try {
                    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $conn = $database->getConnection();
                    
                    // Get vendor count
                    $stmt = $conn->query("SELECT COUNT(*) as vendor_count FROM vendors");
                    $vendor_count = $stmt->fetch(PDO::FETCH_ASSOC)['vendor_count'];
                    
                    // Get recent vendors
                    $stmt = $conn->query("SELECT vendor_name, created_at FROM vendors ORDER BY created_at DESC LIMIT 5");
                    $recent_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                } catch (Exception $e) {
                    $vendor_count = 0;
                    $recent_vendors = [];
                }
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $vendor_count; ?></div>
                            <div class="stats-label">Total Vendors</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Recent Vendors</h4>
                        <?php if (!empty($recent_vendors)): ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($recent_vendors, 0, 3) as $vendor): ?>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm"><?php echo htmlspecialchars($vendor['vendor_name']); ?></span>
                                        <span class="text-xs text-muted">
                                            <?php 
                                            if (!empty($vendor['created_at'])) {
                                                echo date('M j', strtotime($vendor['created_at']));
                                            } else {
                                                echo 'Recently';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">No vendors added yet.</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="text-primary text-sm">View all vendors →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
