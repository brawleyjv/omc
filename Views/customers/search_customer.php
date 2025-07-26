<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/Models/CustomerModel.php';

// Initialize search results
$customers = [];
$searchQuery = '';
$hasSearched = false;

// Handle search form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_query'])) {
    $searchQuery = trim($_POST['search_query']);
    $hasSearched = true;
    
    if (!empty($searchQuery)) {
        try {
            $customerModel = new CustomerModel();
            $customers = $customerModel->searchCustomers($searchQuery);
        } catch (Exception $e) {
            $errorMessage = "Error searching customers: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Customers - OMC</title>
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
                    <h1>Search Customers</h1>
                    <p>Find customers by name, email, phone, or company</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="nav-link">All Customers</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="nav-link">Add Customer</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Customer Search</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="btn btn-primary">
                        <span class="icon">👤</span>
                        Add New Customer
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Customer Search</h2>
                <p class="card-subtitle">Search for customers by name, email, phone number, or company</p>
            </div>
            <div class="card-body">
                <?php if (isset($errorMessage)): ?>
                    <div class="notification notification-error">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_query" class="form-label">Search Query</label>
                            <input type="text" id="search_query" name="search_query" class="form-control" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   placeholder="Enter customer name, email, phone, or company name...">
                            <small class="form-text">Search across all customer fields including name, email, phone, and company</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🔍</span>
                            Search Customers
                        </button>
                        <?php if ($hasSearched): ?>
                            <a href="<?php echo BASE_URL; ?>Views/customers/search_customer.php" class="btn btn-secondary">
                                <span class="icon">🔄</span>
                                Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if ($hasSearched): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Search Results</h2>
                    <p class="card-subtitle">
                        <?php if (empty($customers)): ?>
                            No customers found matching "<?php echo htmlspecialchars($searchQuery); ?>"
                        <?php else: ?>
                            Found <?php echo count($customers); ?> customer(s) matching "<?php echo htmlspecialchars($searchQuery); ?>"
                        <?php endif; ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if (empty($customers)): ?>
                        <div class="notification notification-info">
                            <p><strong>No customers found.</strong></p>
                            <p>Try adjusting your search terms or browse all customers.</p>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="btn btn-primary">
                                    <span class="icon">👥</span>
                                    View All Customers
                                </a>
                                <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="btn btn-ghost">
                                    <span class="icon">➕</span>
                                    Add New Customer
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($customer['name'] ?? ''); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($customer['company'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if (!empty($customer['email'])): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="text-primary">
                                                        <?php echo htmlspecialchars($customer['email']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No email</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($customer['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($customer['phone']); ?>" class="text-primary">
                                                        <?php echo htmlspecialchars($customer['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No phone</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $location = [];
                                                if (!empty($customer['city'])) $location[] = $customer['city'];
                                                if (!empty($customer['state'])) $location[] = $customer['state'];
                                                echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'N/A';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo BASE_URL; ?>Views/customers/view_customer.php?id=<?php echo $customer['id']; ?>" 
                                                       class="btn btn-ghost btn-sm" title="View Customer">
                                                        <span class="icon">👁️</span>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>Views/customers/edit_customer.php?id=<?php echo $customer['id']; ?>" 
                                                       class="btn btn-ghost btn-sm" title="Edit Customer">
                                                        <span class="icon">✏️</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Search Actions -->
                        <div class="form-actions mt-4">
                            <a href="<?php echo BASE_URL; ?>Views/customers/list_customers.php" class="btn btn-secondary">
                                <span class="icon">👥</span>
                                View All Customers
                            </a>
                            <a href="<?php echo BASE_URL; ?>Views/customers/add_customer.php" class="btn btn-primary">
                                <span class="icon">➕</span>
                                Add New Customer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Tips -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Search Tips</h2>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium mb-2">Search by Name</h4>
                        <p class="text-sm text-muted">Enter first name, last name, or full name</p>
                        <code class="text-xs">John Smith</code>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Search by Email</h4>
                        <p class="text-sm text-muted">Enter full or partial email addresses</p>
                        <code class="text-xs">john@company.com</code>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Search by Phone</h4>
                        <p class="text-sm text-muted">Enter phone number with or without formatting</p>
                        <code class="text-xs">555-123-4567</code>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Search by Company</h4>
                        <p class="text-sm text-muted">Enter company or business name</p>
                        <code class="text-xs">ABC Construction</code>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-focus the search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_query');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });

        // Enable search on Enter key
        document.getElementById('search_query').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    </script>
</body>
</html>
