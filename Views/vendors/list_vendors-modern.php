<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Correct relative path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php'; // Corrected path

use MyApp\Controllers\VendorController; // Use the correct namespace
use MyApp\Models\Database; // Add namespace for Database

// Ensure DB constants are defined
if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
    die('Database configuration constants are not defined. Please check config.php.');
}

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Fixed to use DB_PASSWORD
$vendorController = new VendorController($database); // Pass Database instance to VendorController

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vendor_id'])) {
    $vendorId = $_POST['delete_vendor_id'];
    $vendorController->deleteVendor($vendorId);
    header('Location: list_vendors.php');
    exit;
}

$vendors = $vendorController->getVendors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors - Ozark Made Crafts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
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
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="nav-link">Projects</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="nav-link">Materials</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="nav-link">Customers</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="nav-link" style="background-color: rgba(255, 255, 255, 0.1);">Vendors</a>
                <div class="user-info">
                    <?php if (!empty($_SESSION['username'])): ?>
                        <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <form action="<?php echo BASE_URL; ?>public/logout.php" method="post" style="display: inline;">
                            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                        </form>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="card card-primary mb-4">
            <div class="card-header">
                <h1 class="card-title">Vendor Management</h1>
                <p class="card-subtitle">Manage supplier relationships, contact information, and procurement details</p>
            </div>
            <div class="card-footer">
                <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary">
                    <span style="margin-right: 0.5rem;">➕</span>
                    Add New Vendor
                </a>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-outline">
                    <span style="margin-right: 0.5rem;">🏠</span>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Vendors List -->
        <?php if (empty($vendors)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏪</div>
                    <h3 class="text-secondary mb-2">No Vendors Found</h3>
                    <p class="text-secondary mb-3">You haven't added any vendors yet. Start by adding your first vendor.</p>
                    <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary">Add Your First Vendor</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Vendor Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Website</th>
                            <th>Mailing Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">
                                        <?php echo htmlspecialchars($vendor['Vendor']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($vendor['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($vendor['phone']); ?>" class="text-primary" style="text-decoration: none;">
                                            <?php echo htmlspecialchars($vendor['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-secondary">No phone</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($vendor['email_address'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($vendor['email_address']); ?>" class="text-primary" style="text-decoration: none;">
                                            <?php echo htmlspecialchars($vendor['email_address']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-secondary">No email</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($vendor['web_address'])): ?>
                                        <a href="<?php echo htmlspecialchars($vendor['web_address']); ?>" target="_blank" class="text-primary" style="text-decoration: none;">
                                            <?php 
                                            $url = $vendor['web_address'];
                                            $domain = parse_url($url, PHP_URL_HOST) ?: $url;
                                            echo htmlspecialchars($domain);
                                            ?>
                                            <span style="margin-left: 0.25rem;">🔗</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-secondary">No website</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($vendor['mailing_address'])): ?>
                                        <div style="max-width: 200px; word-wrap: break-word;">
                                            <?php echo nl2br(htmlspecialchars($vendor['mailing_address'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-secondary">No address</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <a href="<?php echo BASE_URL; ?>Views/vendors/edit_vendor.php?id=<?php echo $vendor['id']; ?>" 
                                           class="btn btn-ghost btn-sm">
                                            <span>✏️</span>
                                            Edit
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this vendor?');">
                                            <input type="hidden" name="delete_vendor_id" value="<?php echo $vendor['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <span>🗑️</span>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h4 class="text-primary mb-1">Total Vendors: <?php echo count($vendors); ?></h4>
                            <p class="text-secondary mb-0">Active vendor relationships in your system</p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-secondary btn-sm">
                                Add Another Vendor
                            </a>
                            <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="btn btn-outline btn-sm">
                                View Materials
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Add some spacing at the bottom -->
    <div style="height: 2rem;"></div>
</body>
</html>
