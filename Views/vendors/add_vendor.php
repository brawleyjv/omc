<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Correct relative path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vendor - OMC</title>
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
                <div class="header-brand-text">
                    <h1>Add New Vendor</h1>
                    <p>Add a new supplier to your vendor database</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="nav-link">Vendors Home</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="nav-link">All Vendors</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Create New Vendor</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="btn btn-ghost">
                        <span class="icon">🏪</span>
                        View All Vendors
                    </a>
                </div>
            </div>
        </div>

        <!-- Add Vendor Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Vendor Information</h2>
                <p class="card-subtitle">Enter the vendor details and contact information</p>
            </div>
            <div class="card-body">
                <form id="add-vendor-form" action="<?php echo BASE_URL; ?>public/Vendors/insert_vendor.php" method="post">
                    <div class="form-grid">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Basic Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="vendor" class="form-label">Vendor Name *</label>
                                    <input type="text" id="vendor" name="vendor" class="form-control" required placeholder="e.g., Home Depot, Lowes, Local Supplier">
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Contact Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="(555) 123-4567">
                                </div>
                                <div class="form-group">
                                    <label for="email_address" class="form-label">Email Address</label>
                                    <input type="email" id="email_address" name="email_address" class="form-control" placeholder="contact@vendor.com">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="web_address" class="form-label">Website URL</label>
                                    <input type="url" id="web_address" name="web_address" class="form-control" placeholder="https://www.vendor.com">
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Mailing Address</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="mailing_address" class="form-label">Mailing Address</label>
                                    <textarea id="mailing_address" name="mailing_address" class="form-control" rows="3" placeholder="123 Business Street&#10;Suite 100&#10;City, State 12345"></textarea>
                                    <small class="form-text">Enter the complete mailing address for this vendor</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🏪</span>
                            Create Vendor
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="btn btn-secondary">
                            <span class="icon">✖️</span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
