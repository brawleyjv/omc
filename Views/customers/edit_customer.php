<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "Views/customers/index.php?error=" . urlencode('Customer ID is required'));
    exit();
}

$customerId = intval($_GET['id']);
$customer = null;
$success_message = '';
$error_message = '';

try {
    $db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $connection = $db->getConnection();

    if ($connection instanceof PDO) {
        $query = "SELECT * FROM customers WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            header("Location: " . BASE_URL . "Views/customers/index.php?error=" . urlencode('Customer not found'));
            exit();
        }
    } else {
        throw new Exception("Database connection is invalid.");
    }
} catch (Exception $e) {
    error_log("Error fetching customer: " . $e->getMessage());
    header("Location: " . BASE_URL . "Views/customers/index.php?error=" . urlencode('Failed to fetch customer'));
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateQuery = "UPDATE customers SET 
                        name = ?, 
                        Project = ?, 
                        address = ?, 
                        city = ?, 
                        state = ?, 
                        zip = ?, 
                        phone = ?, 
                        email = ?, 
                        notes = ? 
                        WHERE id = ?";
        
        $stmt = $connection->prepare($updateQuery);
        $result = $stmt->execute([
            $_POST['name'],
            $_POST['project_name'],
            $_POST['address'],
            $_POST['city'],
            $_POST['state'],
            $_POST['zip'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['notes'],
            $customerId
        ]);

        if ($result) {
            header("Location: " . BASE_URL . "Views/customers/index.php?success=" . urlencode('Customer updated successfully'));
            exit();
        } else {
            $error_message = 'Failed to update customer.';
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - OMC</title>
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
                    <h1>Edit Customer</h1>
                    <p>Update customer information and contact details</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="nav-link">Customers</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Edit Customer</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="btn btn-secondary">
                        <span class="icon">👥</span>
                        Back to Customers
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <div class="notification notification-error">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Customer Information</h2>
                <p class="card-subtitle">Modify customer details and contact information</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>Views/customers/edit_customer.php?id=<?php echo $customerId; ?>" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($customer['id'] ?? ''); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name" class="form-label required">Customer Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" 
                                   maxlength="100" required placeholder="Enter customer name...">
                            <small class="form-text">Full name of the customer</small>
                        </div>

                        <div class="form-group">
                            <label for="project_name" class="form-label">Project Name</label>
                            <input type="text" id="project_name" name="project_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($customer['Project'] ?? ''); ?>" 
                                   maxlength="100" placeholder="Enter project name...">
                            <small class="form-text">Primary project or job name</small>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" 
                                   maxlength="15" placeholder="Enter phone number...">
                            <small class="form-text">Primary contact phone number</small>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" 
                                   maxlength="100" placeholder="Enter email address...">
                            <small class="form-text">Primary contact email</small>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="form-section mt-6">
                        <h3 class="form-section-title">Address Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group md:col-span-2">
                                <label for="address" class="form-label">Street Address</label>
                                <input type="text" id="address" name="address" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>" 
                                       maxlength="200" placeholder="Enter street address...">
                                <small class="form-text">Street address or P.O. Box</small>
                            </div>

                            <div class="form-group">
                                <label for="city" class="form-label">City</label>
                                <input type="text" id="city" name="city" class="form-control" 
                                       value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" 
                                       maxlength="100" placeholder="Enter city...">
                                <small class="form-text">City name</small>
                            </div>

                            <div class="form-group">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" id="state" name="state" class="form-control" 
                                               value="<?php echo htmlspecialchars($customer['state'] ?? ''); ?>" 
                                               maxlength="50" placeholder="State...">
                                        <small class="form-text">State or province</small>
                                    </div>
                                    <div>
                                        <label for="zip" class="form-label">Zip Code</label>
                                        <input type="text" id="zip" name="zip" class="form-control" 
                                               value="<?php echo htmlspecialchars($customer['zip'] ?? ''); ?>" 
                                               maxlength="10" placeholder="Zip code...">
                                        <small class="form-text">Postal code</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="form-group mt-6">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="4" 
                                  maxlength="500" placeholder="Enter any additional notes or comments..."><?php echo htmlspecialchars($customer['notes'] ?? ''); ?></textarea>
                        <small class="form-text">Additional information or special instructions</small>
                    </div>

                    <div class="form-actions mt-6">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">💾</span>
                            Update Customer
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="btn btn-secondary">
                            <span class="icon">❌</span>
                            Cancel
                        </a>
                        <button type="button" onclick="confirmDelete(<?php echo $customer['id']; ?>)" class="btn btn-danger">
                            <span class="icon">🗑️</span>
                            Delete Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(customerId) {
            if (confirm('Are you sure you want to delete this customer? This action cannot be undone and will also delete any associated projects.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/customers/delete_customer.php?id=' + customerId;
            }
        }
    </script>
</body>
</html>
