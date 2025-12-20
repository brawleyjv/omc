<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

// Get estimate by ID
$estimateId = $_GET['id'] ?? null;
$estimate = null;

if ($estimateId) {
    try {
        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn = $database->getPdo();
        $estimateModel = new EstimateModel($conn);
        $estimate = $estimateModel->getEstimateById($estimateId);
        
        // Get rates to calculate individual costs
        $ratesQuery = "SELECT mill_rate, laser_rate, labor_rate, bit_change_rate, customize_rate FROM setup LIMIT 1";
        $ratesStmt = $conn->prepare($ratesQuery);
        $ratesStmt->execute();
        $rates = $ratesStmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate individual machine costs
        $routerCost = $estimate['router_time'] * ($rates['mill_rate'] ?? 0.85);
        $laserCost = $estimate['laser_time'] * ($rates['laser_rate'] ?? 0.50);
        $bitChangeCost = ($estimate['bit_changes'] ?? 0) * ($rates['bit_change_rate'] ?? 5.00);
        $customizationCost = ($estimate['needs_customization'] ?? 0) ? ($rates['customize_rate'] ?? 5.00) : 0;
        
    } catch (Exception $e) {
        error_log("Error loading estimate: " . $e->getMessage());
    }
}

if (!$estimate) {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate <?php echo htmlspecialchars($estimate['estimate_number']); ?> - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .estimate-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-draft { background: #6c757d; color: white; }
        .status-sent { background: #0d6efd; color: white; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .total-box {
            background: #e7f3ff;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            margin-top: 1rem;
        }
        .total-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #0066cc;
        }
        @media print {
            .no-print { display: none; }
            .header { display: none; }
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header no-print">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>View Estimate</h1>
                    <p><?php echo htmlspecialchars($estimate['estimate_number']); ?></p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="nav-link">Estimates Home</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="nav-link">All Estimates</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/create_new_estimate.php" class="nav-link">Create New</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        
        <?php if (isset($_GET['success'])): ?>
        <div class="notification notification-success mb-4 no-print">
            ✅ Estimate created successfully! 
            <a href="<?php echo BASE_URL; ?>Views/estimate/print_estimate.php?id=<?php echo $estimate['id']; ?>" target="_blank" style="margin-left: 15px; text-decoration: underline; color: inherit; font-weight: bold;">
                🖨️ Click here to print now
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Estimate Header -->
        <div class="estimate-header">
            <h1>Estimate #<?php echo htmlspecialchars($estimate['estimate_number']); ?></h1>
            <div class="info-grid">
                <div>
                    <strong>Customer:</strong><br>
                    <?php echo htmlspecialchars($estimate['customer_name']); ?><br>
                    <?php if ($estimate['customer_email']): ?>
                        <?php echo htmlspecialchars($estimate['customer_email']); ?><br>
                    <?php endif; ?>
                    <?php if ($estimate['customer_phone']): ?>
                        <?php echo htmlspecialchars($estimate['customer_phone']); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Project:</strong><br>
                    <?php echo htmlspecialchars($estimate['project_name']); ?>
                </div>
                <div>
                    <strong>Date:</strong><br>
                    <?php echo date('F d, Y', strtotime($estimate['created_at'])); ?>
                </div>
                <div>
                    <strong>Status:</strong><br>
                    <span class="status-badge status-<?php echo htmlspecialchars($estimate['status']); ?>">
                        <?php echo ucfirst($estimate['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($estimate['project_description']): ?>
        <!-- Project Description -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Project Description</h2>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($estimate['project_description'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Time Breakdown -->
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Labor & Machine Time</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Time</th>
                                <th style="text-align: right;">Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CNC Router Time</td>
                                <td><?php echo number_format($estimate['router_time'], 2); ?> minutes</td>
                                <td style="text-align: right;">$<?php echo number_format($routerCost, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Laser Time</td>
                                <td><?php echo number_format($estimate['laser_time'], 2); ?> minutes</td>
                                <td style="text-align: right;">$<?php echo number_format($laserCost, 2); ?></td>
                            </tr>
                            <tr>
                                <td>Labor Hours</td>
                                <td><?php echo number_format($estimate['labor_hours'], 2); ?> hours</td>
                                <td style="text-align: right;">$<?php echo number_format($estimate['labor_cost'], 2); ?></td>
                            </tr>
                            <?php if ($bitChangeCost > 0): ?>
                            <tr>
                                <td>Bit Changes</td>
                                <td><?php echo ($estimate['bit_changes'] ?? 0); ?> changes</td>
                                <td style="text-align: right;">$<?php echo number_format($bitChangeCost, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($customizationCost > 0): ?>
                            <tr>
                                <td>Customization</td>
                                <td>Yes</td>
                                <td style="text-align: right;">$<?php echo number_format($customizationCost, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (($estimate['shipping_cost'] ?? 0) > 0): ?>
                            <tr>
                                <td>Shipping/Packaging</td>
                                <td>-</td>
                                <td style="text-align: right;">$<?php echo number_format($estimate['shipping_cost'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Materials -->
        <?php if (!empty($estimate['materials'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Materials</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Quantity</th>
                                <th>Unit Type</th>
                                <th>Unit Cost</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estimate['materials'] as $material): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                                <td><?php echo number_format($material['quantity'], 2); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $material['unit_type'])); ?></td>
                                <td>$<?php echo number_format($material['unit_cost'], 2); ?></td>
                                <td>$<?php echo number_format($material['total_cost'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" style="text-align: right;"><strong>Materials Total:</strong></td>
                                <td><strong>$<?php echo number_format($estimate['materials_cost'], 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Custom Items -->
        <?php if (!empty($estimate['custom_items'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title">Additional Items</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $customTotal = 0;
                            foreach ($estimate['custom_items'] as $item): 
                                $customTotal += $item['cost'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td>$<?php echo number_format($item['cost'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td style="text-align: right;"><strong>Additional Items Total:</strong></td>
                                <td><strong>$<?php echo number_format($customTotal, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Total -->
        <div class="total-box">
            <h3>Total Estimate</h3>
            <div class="total-amount">$<?php echo number_format($estimate['total_estimate'], 2); ?></div>
        </div>

        <?php if ($estimate['notes']): ?>
        <!-- Internal Notes -->
        <div class="card mt-4 no-print">
            <div class="card-header">
                <h2 class="card-title">Internal Notes</h2>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($estimate['notes'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="form-actions mt-4 no-print">
            <a href="<?php echo BASE_URL; ?>Views/estimate/print_estimate.php?id=<?php echo $estimate['id']; ?>" target="_blank" class="btn btn-primary">
                <span class="icon">🖨️</span> Print Estimate
            </a>
            <a href="<?php echo BASE_URL; ?>Views/estimate/email_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-primary">
                <span class="icon">📧</span> Email to Customer
            </a>
            <a href="<?php echo BASE_URL; ?>Views/estimate/edit_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-secondary">
                <span class="icon">✏️</span> Edit Estimate
            </a>
            <a href="<?php echo BASE_URL; ?>public/Estimate/clone_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-secondary">
                <span class="icon">📋</span> Clone for Customer
            </a>
            
            <?php if ($estimate['status'] == 'draft'): ?>
            <a href="<?php echo BASE_URL; ?>public/Estimate/update_status.php?id=<?php echo $estimate['id']; ?>&status=sent" class="btn btn-success">
                <span class="icon">📧</span> Mark as Sent
            </a>
            <?php endif; ?>
            
            <?php if ($estimate['status'] == 'sent'): ?>
            <a href="<?php echo BASE_URL; ?>public/Estimate/update_status.php?id=<?php echo $estimate['id']; ?>&status=approved" class="btn btn-success">
                <span class="icon">✅</span> Approve
            </a>
            <a href="<?php echo BASE_URL; ?>public/Estimate/update_status.php?id=<?php echo $estimate['id']; ?>&status=rejected" class="btn btn-danger">
                <span class="icon">❌</span> Reject
            </a>
            <?php endif; ?>
            
            <?php if ($estimate['status'] == 'approved' && !$estimate['project_id']): ?>
            <a href="<?php echo BASE_URL; ?>public/Estimate/convert_to_project.php?id=<?php echo $estimate['id']; ?>" class="btn btn-success">
                <span class="icon">🚀</span> Convert to Project
            </a>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="btn btn-ghost">
                <span class="icon">📋</span> All Estimates
            </a>
        </div>
    </main>
</body>
</html>