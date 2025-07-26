<?php
require_once __DIR__ . '/../../config.php'; // Correct relative path to config.php
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
    <title>List of Vendors - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Vendor Management</h1>
                    <p>Manage supplier relationships and procurement information</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="nav-link">Vendors Home</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Vendor List</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary">
                        <span class="icon">➕</span>
                        Add Vendor
                    </a>
                </div>
            </div>
        </div>

        <!-- Vendors Table Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Vendors</h2>
                <p class="card-subtitle">Manage your supplier database and contact information</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vendor Name</th>
                                <th>Phone</th>
                                <th>Website</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vendors)): ?>
                                <?php foreach ($vendors as $vendor): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($vendor['id']); ?></span>
                                        </td>
                                        <td>
                                            <div class="vendor-info">
                                                <strong><?php echo htmlspecialchars($vendor['Vendor']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($vendor['phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($vendor['phone']); ?>" class="text-link">
                                                    <?php echo htmlspecialchars($vendor['phone']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($vendor['web_address'])): ?>
                                                <a href="<?php echo htmlspecialchars($vendor['web_address']); ?>" target="_blank" class="text-link">
                                                    Visit Website
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($vendor['mailing_address'])): ?>
                                                <span class="text-truncate" title="<?php echo htmlspecialchars($vendor['mailing_address']); ?>">
                                                    <?php echo htmlspecialchars($vendor['mailing_address']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($vendor['email_address'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($vendor['email_address']); ?>" class="text-link">
                                                    <?php echo htmlspecialchars($vendor['email_address']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_vendor.php?vendor_id=<?php echo htmlspecialchars($vendor['id']); ?>" 
                                                   class="btn btn-ghost btn-sm" title="Edit Vendor">
                                                    <span class="icon">✏️</span>
                                                    Edit
                                                </a>
                                                <form action="list_vendors.php" method="post" 
                                                      onsubmit="return confirm('Are you sure you want to delete this vendor?');" 
                                                      style="display:inline;">
                                                    <input type="hidden" name="delete_vendor_id" value="<?php echo htmlspecialchars($vendor['id']); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Vendor">
                                                        <span class="icon">🗑️</span>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="empty-state">
                                            <p class="text-muted">No vendors found.</p>
                                            <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn btn-primary btn-sm mt-2">
                                                Add Your First Vendor
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>