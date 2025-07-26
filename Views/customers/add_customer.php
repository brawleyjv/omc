<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer - OMC</title>
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
                    <h1>Add New Customer</h1>
                    <p>Create a new customer record with contact and project information</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="nav-link">Customers Home</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="nav-link">All Customers</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Create New Customer</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="btn btn-ghost">
                        <span class="icon">👥</span>
                        View All Customers
                    </a>
                </div>
            </div>
        </div>

        <!-- Add Customer Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Customer Information</h2>
                <p class="card-subtitle">Enter the customer details and project information</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>public/customers/add_customer.php" method="post">
                    <div class="form-grid">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Basic Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Customer Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" maxlength="100" required placeholder="Enter full customer name">
                                </div>
                                <div class="form-group">
                                    <label for="project_name" class="form-label">Project Name *</label>
                                    <input type="text" id="project_name" name="project_name" class="form-control" maxlength="100" required placeholder="Enter project name">
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Contact Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" id="phone" name="phone" class="form-control" maxlength="15" placeholder="(555) 123-4567">
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" maxlength="100" placeholder="customer@example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Address Information</h3>
                            
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label for="address" class="form-label">Street Address</label>
                                    <input type="text" id="address" name="address" class="form-control" maxlength="200" placeholder="123 Main Street">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" id="city" name="city" class="form-control" maxlength="100" placeholder="City">
                                </div>
                                <div class="form-group">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" id="state" name="state" class="form-control" maxlength="50" placeholder="State">
                                </div>
                                <div class="form-group">
                                    <label for="zip" class="form-label">Zip Code</label>
                                    <input type="text" id="zip" name="zip" class="form-control" maxlength="10" placeholder="12345">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Additional Information</h3>
                            
                            <div class="form-group">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="4" maxlength="500" placeholder="Add any additional notes about the customer or project..."></textarea>
                                <small class="form-text">Optional notes about the customer or project requirements</small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">👥</span>
                            Create Customer
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="btn btn-secondary">
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