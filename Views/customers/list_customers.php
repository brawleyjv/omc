<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . 'Models/Database.php'; // Include the Database class

use MyApp\Models\Database;

$customers = []; // Initialize customers array
try {
    $db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connection = $db->getConnection();

    if ($connection) { // Ensure connection is valid
        $query = "SELECT * FROM customers ORDER BY name ASC";
        $stmt = $connection->query($query);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        throw new Exception("Database connection is null.");
    }
} catch (Exception $e) {
    error_log("Error fetching customers: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Customers - OMC</title>
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
                    <h1>Customer Management</h1>
                    <p>Manage customer information and project history</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="nav-link">Customers Home</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Customer List</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="btn btn-primary">
                        <span class="icon">👤</span>
                        Add Customer
                    </a>
                </div>
            </div>
        </div>

        <!-- Customers Table Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Customers</h2>
                <p class="card-subtitle">Manage your customer database and contact information</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Project</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($customers)): ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($customer['id'] ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <strong><?php echo htmlspecialchars($customer['name'] ?? ''); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['Project'])): ?>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($customer['Project']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No project assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <?php if (!empty($customer['phone'])): ?>
                                                    <div>
                                                        <span class="icon">📞</span>
                                                        <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>" class="text-link">
                                                            <?php echo htmlspecialchars($customer['phone']); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($customer['email'])): ?>
                                                    <div>
                                                        <span class="icon">📧</span>
                                                        <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="text-link">
                                                            <?php echo htmlspecialchars($customer['email']); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (empty($customer['phone']) && empty($customer['email'])): ?>
                                                    <span class="text-muted">No contact info</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="location-info">
                                                <?php 
                                                $address_parts = array_filter([
                                                    $customer['address'] ?? '',
                                                    $customer['city'] ?? '',
                                                    $customer['state'] ?? '',
                                                    $customer['zip'] ?? ''
                                                ]);
                                                if (!empty($address_parts)): ?>
                                                    <span class="text-truncate" title="<?php echo htmlspecialchars(implode(', ', $address_parts)); ?>">
                                                        <?php echo htmlspecialchars(implode(', ', $address_parts)); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No address</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['notes'])): ?>
                                                <span class="text-truncate" title="<?php echo htmlspecialchars($customer['notes']); ?>">
                                                    <?php echo htmlspecialchars(substr($customer['notes'], 0, 50)) . (strlen($customer['notes']) > 50 ? '...' : ''); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php?customer=<?php echo urlencode($customer['name']); ?>" 
                                                   class="btn btn-primary btn-sm" title="View Estimates">
                                                    <span class="icon">📋</span>
                                                    Estimates
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>Views/customers/edit_customer.php?id=<?php echo htmlspecialchars($customer['id'] ?? ''); ?>" 
                                                   class="btn btn-ghost btn-sm" title="Edit Customer">
                                                    <span class="icon">✏️</span>
                                                    Edit
                                                </a>
                                                <form action="<?php echo BASE_URL; ?>public/customers/delete_customer.php" method="post" 
                                                      onsubmit="return confirm('Are you sure you want to delete this customer?');" 
                                                      style="display:inline;">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer['id'] ?? ''); ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Customer">
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
                                            <p class="text-muted">No customers found.</p>
                                            <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="btn btn-primary btn-sm mt-2">
                                                Add Your First Customer
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
