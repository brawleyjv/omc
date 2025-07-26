<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

use MyApp\Models\Database;
use MyApp\Controllers\VendorController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$vendorController = new VendorController($database);

$vendor = null;
$success_message = '';
$error_message = '';

if (isset($_GET['vendor_id'])) {
    $vendorId = $_GET['vendor_id'];
    $vendor = $vendorController->getVendorById($vendorId);
    if (!$vendor) {
        header("Location: " . BASE_URL . "Views/vendors/index.php?error=" . urlencode('Vendor not found'));
        exit;
    }
} else {
    header("Location: " . BASE_URL . "Views/vendors/index.php?error=" . urlencode('No vendor ID provided'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $vendorId = $_POST['vendor_id'];
        $vendorName = $_POST['vendor'];
        $vendorPhone = $_POST['phone'];
        $vendorWebAddress = $_POST['web_address'];
        $vendorMailingAddress = $_POST['mailing_address'];
        $vendorEmailAddress = $_POST['email_address'];

        $result = $vendorController->updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress);

        if ($result) {
            header("Location: " . BASE_URL . "Views/vendors/index.php?success=" . urlencode('Vendor updated successfully'));
            exit;
        } else {
            $error_message = 'Failed to update vendor.';
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
    <title>Edit Vendor - OMC</title>
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
                    <h1>Edit Vendor</h1>
                    <p>Update vendor information and contact details</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="nav-link">Vendors</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Edit Vendor</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn btn-secondary">
                        <span class="icon">🏢</span>
                        Back to Vendors
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
        <?php if ($vendor): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Update Vendor Information</h2>
                    <p class="card-subtitle">Modify vendor details and contact information</p>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>Views/vendors/edit_vendor.php?vendor_id=<?php echo $vendorId; ?>" method="post">
                        <input type="hidden" name="vendor_id" value="<?php echo htmlspecialchars($vendor['id'] ?? ''); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="form-group">
                                <label for="vendor_id_display" class="form-label">Vendor ID</label>
                                <input type="text" id="vendor_id_display" class="form-control" 
                                       value="<?php echo htmlspecialchars($vendor['id'] ?? ''); ?>" readonly>
                                <small class="form-text">System-generated vendor identifier</small>
                            </div>

                            <div class="form-group">
                                <label for="vendor" class="form-label required">Vendor Name</label>
                                <input type="text" id="vendor" name="vendor" class="form-control" 
                                       value="<?php echo htmlspecialchars($vendor['Vendor'] ?? ''); ?>" required
                                       placeholder="Enter vendor name...">
                                <small class="form-text">Company or business name</small>
                            </div>

                            <!-- Contact Information -->
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($vendor['phone'] ?? ''); ?>"
                                       placeholder="Enter phone number...">
                                <small class="form-text">Primary contact phone number</small>
                            </div>

                            <div class="form-group">
                                <label for="email_address" class="form-label">Email Address</label>
                                <input type="email" id="email_address" name="email_address" class="form-control" 
                                       value="<?php echo htmlspecialchars($vendor['email_address'] ?? ''); ?>"
                                       placeholder="Enter email address...">
                                <small class="form-text">Primary contact email</small>
                            </div>

                            <!-- Address Information -->
                            <div class="form-group">
                                <label for="mailing_address" class="form-label">Mailing Address</label>
                                <textarea id="mailing_address" name="mailing_address" class="form-control" rows="3"
                                          placeholder="Enter mailing address..."><?php echo htmlspecialchars($vendor['mailing_address'] ?? ''); ?></textarea>
                                <small class="form-text">Complete mailing address</small>
                            </div>

                            <div class="form-group">
                                <label for="web_address" class="form-label">Website URL</label>
                                <input type="url" id="web_address" name="web_address" class="form-control" 
                                       value="<?php echo htmlspecialchars($vendor['web_address'] ?? ''); ?>"
                                       placeholder="https://example.com">
                                <small class="form-text">Company website URL</small>
                            </div>
                        </div>

                        <div class="form-actions mt-6">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon">💾</span>
                                Update Vendor
                            </button>
                            <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn btn-secondary">
                                <span class="icon">❌</span>
                                Cancel
                            </a>
                            <button type="button" onclick="confirmDelete(<?php echo $vendor['id']; ?>)" class="btn btn-danger">
                                <span class="icon">🗑️</span>
                                Delete Vendor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-6xl">❌</span>
                    </div>
                    <h3 class="card-title">Vendor Not Found</h3>
                    <p class="text-muted mb-6">The requested vendor could not be found.</p>
                    <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn btn-primary">
                        <span class="icon">🏢</span>
                        Back to Vendors List
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function confirmDelete(vendorId) {
            if (confirm('Are you sure you want to delete this vendor? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/vendors/delete_vendor.php?vendor_id=' + vendorId;
            }
        }
    </script>
</body>
</html>
