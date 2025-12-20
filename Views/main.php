<?php
ob_start(); // Start output buffering to prevent premature output

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class
require_once BASE_PATH . 'Models/EtsyModel.php'; // Include Etsy Model

use MyApp\Models\Database; // Add this to use the Database class from the namespace
use MyApp\Models\EtsyModel;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed

// Get Etsy connection status
$pdo = $database->getPdo();
$etsyModel = new EtsyModel($pdo);
$etsyConnected = $etsyModel->isConnected();

// Get Etsy shop info
$etsyQuery = "SELECT etsy_shop_name, etsy_last_sync FROM settings WHERE id = 1";
$etsyStmt = $pdo->prepare($etsyQuery);
$etsyStmt->execute();
$etsyData = $etsyStmt->fetch(PDO::FETCH_ASSOC);
$etsyShopName = $etsyData['etsy_shop_name'] ?? null;
$etsyLastSync = $etsyData['etsy_last_sync'] ?? null;

// Get recent Etsy orders count (only if connected)
$recentOrdersCount = 0;
if ($etsyConnected) {
    $ordersQuery = "SELECT COUNT(*) as count FROM etsy_orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $ordersStmt = $pdo->prepare($ordersQuery);
    $ordersStmt->execute();
    $ordersData = $ordersStmt->fetch(PDO::FETCH_ASSOC);
    $recentOrdersCount = $ordersData['count'] ?? 0;
}

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dynamically resolve paths
require_once BASE_PATH . 'auth/check_auth.php'; // Corrected path to check_auth.php

// Log session details for debugging
error_log("Main.php: Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMC Dashboard - Ozark Made Crafts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        /* Icons using CSS shapes for now - can be replaced with icon font */
        .icon {
            display: inline-block;
            font-style: normal;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <img src="<?php echo BASE_URL; ?>public/images/login-image.png" alt="OMC Logo" onerror="this.style.display='none'">
                <div class="header-brand-text">
                    <h1><?php echo isset($_SESSION['company_name']) ? htmlspecialchars($_SESSION['company_name']) : 'Ozark Made Crafts'; ?></h1>
                    <p><?php echo isset($_SESSION['company_slogan']) ? htmlspecialchars($_SESSION['company_slogan']) : 'Precision Craftsmanship with a Personal Touch'; ?></p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link" style="background-color: rgba(255, 255, 255, 0.1);">Dashboard</a>
                <div class="user-info">
                    <?php if (!empty($_SESSION['username'])): ?>
                        <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <form action="<?php echo BASE_URL; ?>public/logout.php" method="post" style="display: inline;">
                            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>Views/Users/login.php" class="btn btn-outline btn-sm">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Welcome Section -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h2 class="card-title">Project Management Dashboard</h2>
                <p class="card-subtitle">Manage your woodworking projects, materials, and business operations</p>
            </div>
        </div>

        <!-- Main Navigation Cards -->
        <div class="menu-grid">
            <!-- Projects Card -->
            <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">📋</span>
                </div>
                <h3 class="menu-card-title">Projects</h3>
                <p class="menu-card-description">Create, manage, and track your woodworking projects from design to completion</p>
            </a>

            <!-- Materials Card -->
            <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">🪵</span>
                </div>
                <h3 class="menu-card-title">Materials</h3>
                <p class="menu-card-description">Manage inventory, track material costs, and plan material requirements</p>
            </a>

            <!-- Customers Card -->
            <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">👥</span>
                </div>
                <h3 class="menu-card-title">Customers</h3>
                <p class="menu-card-description">Manage customer information, project history, and communication</p>
            </a>

            <!-- Equipment Card -->
            <a href="<?php echo BASE_URL; ?>Views/equipment/index.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">🔧</span>
                </div>
                <h3 class="menu-card-title">Equipment</h3>
                <p class="menu-card-description">Track equipment usage, maintenance schedules, and operational costs</p>
            </a>

            <!-- Users Card -->
            <a href="<?php echo BASE_URL; ?>Views/users/index.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">👤</span>
                </div>
                <h3 class="menu-card-title">Users</h3>
                <p class="menu-card-description">Manage user accounts, permissions, and team member information</p>
            </a>

            <!-- Vendors Card -->
            <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="menu-card">
                <div class="menu-card-icon">
                    <span class="icon">🏪</span>
                </div>
                <h3 class="menu-card-title">Vendors</h3>
                <p class="menu-card-description">Manage supplier relationships, pricing, and procurement information</p>
            </a>

            <!-- Etsy Integration Card -->
            <?php if ($etsyConnected): ?>
                <a href="<?php echo BASE_URL; ?>public/etsy/dashboard.php" class="menu-card" style="border-left: 4px solid #f56400;">
                    <div class="menu-card-icon">
                        <span class="icon">🛒</span>
                    </div>
                    <h3 class="menu-card-title">Etsy Sales</h3>
                    <p class="menu-card-description">
                        <strong style="color: #10b981;">● Connected</strong> - <?php echo htmlspecialchars($etsyShopName ?? 'Shop'); ?><br>
                        <small style="color: #6b7280;">
                            <?php if ($recentOrdersCount > 0): ?>
                                <?php echo $recentOrdersCount; ?> order<?php echo $recentOrdersCount !== 1 ? 's' : ''; ?> this week
                            <?php else: ?>
                                No recent orders
                            <?php endif; ?>
                        </small>
                    </p>
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>Views/settings.php#etsy" class="menu-card" style="border-left: 4px solid #94a3b8;">
                    <div class="menu-card-icon">
                        <span class="icon">🛒</span>
                    </div>
                    <h3 class="menu-card-title">Etsy Integration</h3>
                    <p class="menu-card-description">
                        <strong style="color: #94a3b8;">○ Not Connected</strong><br>
                        <small style="color: #6b7280;">Connect your Etsy shop to sync orders</small>
                    </p>
                </a>
            <?php endif; ?>
        </div>

        <!-- Tools & Calculators Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tools & Calculators</h3>
                <p class="card-subtitle">Specialized tools for woodworking calculations and project planning</p>
            </div>
            <div class="card-body">
                <div class="menu-grid">
                    <!-- Board Feet Calculator -->
                    <a href="<?php echo BASE_URL; ?>Views/projects/boardfeet.php" class="menu-card">
                        <div class="menu-card-icon">
                            <span class="icon">📏</span>
                        </div>
                        <h4 class="menu-card-title">Board Feet</h4>
                        <p class="menu-card-description">Calculate board feet for lumber pricing and planning</p>
                    </a>

                    <!-- Scale Project -->
                    <a href="<?php echo BASE_URL; ?>Views/Scale.php" class="menu-card">
                        <div class="menu-card-icon">
                            <span class="icon">📐</span>
                        </div>
                        <h4 class="menu-card-title">Scale Project</h4>
                        <p class="menu-card-description">Scale project dimensions and calculate material adjustments</p>
                    </a>

                    <!-- Estimate -->
                    <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="menu-card">
                        <div class="menu-card-icon">
                            <span class="icon">💰</span>
                        </div>
                        <h4 class="menu-card-title">Estimate</h4>
                        <p class="menu-card-description">Generate project estimates and cost calculations</p>
                    </a>

                    <!-- Chipload Calculator -->
                    <a href="<?php echo BASE_URL; ?>Views/Chipload/chipload.php" class="menu-card">
                        <div class="menu-card-icon">
                            <span class="icon">⚙️</span>
                        </div>
                        <h4 class="menu-card-title">Chipload</h4>
                        <p class="menu-card-description">Calculate optimal cutting speeds and feed rates</p>
                    </a>

                    <!-- Rotary Setup -->
                    <a href="<?php echo BASE_URL; ?>Views/rotary/rotary_setup.php" class="menu-card">
                        <div class="menu-card-icon">
                            <span class="icon">🔄</span>
                        </div>
                        <h4 class="menu-card-title">Rotary Setup</h4>
                        <p class="menu-card-description">Configure rotary axis settings and calculations</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Administration -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">System Administration</h3>
                <p class="card-subtitle">System configuration and maintenance tools</p>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="<?php echo BASE_URL; ?>Views/settings.php" class="btn btn-primary">
                        <span class="icon">💲</span>
                        Pricing Settings
                    </a>
                    <a href="<?php echo BASE_URL; ?>Views/update.php" class="btn btn-outline">
                        <span class="icon">🔄</span>
                        System Update
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="card-grid mt-4">
            <!-- Recent Projects -->
            <div class="card card-primary">
                <div class="card-header">
                    <h4 class="card-title">Recent Projects</h4>
                </div>
                <div class="card-body">
                    <p class="text-secondary">View and manage your most recent projects</p>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-primary btn-sm">View All Projects</a>
                    </div>
                </div>
            </div>

            <!-- Material Inventory -->
            <div class="card card-success">
                <div class="card-header">
                    <h4 class="card-title">Material Status</h4>
                </div>
                <div class="card-body">
                    <p class="text-secondary">Check current material inventory levels</p>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="btn btn-secondary btn-sm">View Inventory</a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card card-warning">
                <div class="card-header">
                    <h4 class="card-title">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <p class="text-secondary">Frequently used actions and shortcuts</p>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="btn btn-ghost btn-sm">New Project</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add some spacing at the bottom -->
    <div style="height: 2rem;"></div>
</body>
</html>