<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

// Get all estimates
try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    $estimateModel = new EstimateModel($conn);
    $estimates = $estimateModel->getAllEstimates();
} catch (Exception $e) {
    error_log("Error loading estimates: " . $e->getMessage());
    $estimates = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Estimates - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-draft { background: #6c757d; color: white; }
        .status-sent { background: #0d6efd; color: white; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .status-converted { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Estimates</h1>
                    <p>Manage all project estimates and quotes</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php" class="nav-link">Create Estimate</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">All Estimates</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php" class="btn btn-primary">
                        <span class="icon">➕</span>
                        New Estimate
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="notification notification-success mb-4">
                Estimate created successfully!
            </div>
        <?php endif; ?>

        <!-- Estimates Table -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($estimates)): ?>
                    <div class="notification notification-info">
                        No estimates found. <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php">Create your first estimate</a>.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Estimate #</th>
                                    <th>Customer</th>
                                    <th>Project</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estimates as $estimate): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($estimate['estimate_number']); ?></td>
                                        <td><?php echo htmlspecialchars($estimate['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($estimate['project_name']); ?></td>
                                        <td>$<?php echo number_format($estimate['total_estimate'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars($estimate['status']); ?>">
                                                <?php echo ucfirst($estimate['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($estimate['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>Views/estimate/view_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>