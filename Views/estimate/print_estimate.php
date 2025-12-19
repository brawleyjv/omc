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
    } catch (Exception $e) {
        error_log("Error loading estimate: " . $e->getMessage());
        die("Error loading estimate");
    }
}

if (!$estimate) {
    die("Estimate not found");
}

// Set headers for PDF download
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estimate <?php echo htmlspecialchars($estimate['estimate_number']); ?></title>
    <style>
        @page { margin: 20mm; }
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
        }
        .estimate-title {
            font-size: 28px;
            color: #333;
            margin: 10px 0;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #0066cc;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin: 5px 0;
        }
        .total-label {
            display: table-cell;
            font-weight: bold;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }
        .grand-total {
            font-size: 24px;
            color: #0066cc;
            border-top: 2px solid #0066cc;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .notes {
            background: #fffbea;
            padding: 15px;
            border-left: 4px solid #f39c12;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">OZARK MADE CRAFTS</div>
        <div class="estimate-title">PROJECT ESTIMATE</div>
        <div><?php echo htmlspecialchars($estimate['estimate_number']); ?></div>
    </div>

    <!-- Customer & Estimate Info -->
    <div class="info-section">
        <div class="info-left">
            <div class="info-box">
                <div class="info-label">CUSTOMER</div>
                <div><strong><?php echo htmlspecialchars($estimate['customer_name']); ?></strong></div>
                <?php if ($estimate['customer_email']): ?>
                <div><?php echo htmlspecialchars($estimate['customer_email']); ?></div>
                <?php endif; ?>
                <?php if ($estimate['customer_phone']): ?>
                <div><?php echo htmlspecialchars($estimate['customer_phone']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="info-right">
            <div class="info-box">
                <div class="info-label">ESTIMATE DETAILS</div>
                <div><strong>Date:</strong> <?php echo date('F d, Y', strtotime($estimate['created_at'])); ?></div>
                <div><strong>Project:</strong> <?php echo htmlspecialchars($estimate['project_name']); ?></div>
                <div><strong>Status:</strong> <?php echo ucfirst($estimate['status']); ?></div>
            </div>
        </div>
    </div>

    <!-- Project Description -->
    <?php if ($estimate['project_description']): ?>
    <div class="notes">
        <div class="info-label">PROJECT DESCRIPTION</div>
        <p><?php echo nl2br(htmlspecialchars($estimate['project_description'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Materials -->
    <?php if (!empty($estimate['materials'])): ?>
    <h3>Materials</h3>
    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th>Quantity</th>
                <th>Unit Type</th>
                <th>Unit Cost</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($estimate['materials'] as $material): ?>
            <tr>
                <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                <td><?php echo number_format($material['quantity'], 2); ?></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $material['unit_type'])); ?></td>
                <td>$<?php echo number_format($material['unit_cost'], 2); ?></td>
                <td class="text-right">$<?php echo number_format($material['total_cost'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="text-right"><strong>Materials Subtotal:</strong></td>
                <td class="text-right"><strong>$<?php echo number_format($estimate['materials_cost'], 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Labor & Machine Time -->
    <h3>Labor & Machine Time</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Time</th>
                <th class="text-right">Cost</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CNC Router Time</td>
                <td><?php echo number_format($estimate['router_time'], 2); ?> minutes</td>
                <td class="text-right" rowspan="2">$<?php echo number_format($estimate['machine_cost'], 2); ?></td>
            </tr>
            <tr>
                <td>Laser Time</td>
                <td><?php echo number_format($estimate['laser_time'], 2); ?> minutes</td>
            </tr>
            <tr>
                <td>Labor Hours</td>
                <td><?php echo number_format($estimate['labor_hours'], 2); ?> hours</td>
                <td class="text-right">$<?php echo number_format($estimate['labor_cost'], 2); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Additional Items -->
    <?php if (!empty($estimate['custom_items'])): ?>
    <h3>Additional Items</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Cost</th>
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
                <td class="text-right">$<?php echo number_format($item['cost'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td class="text-right"><strong>Additional Items Subtotal:</strong></td>
                <td class="text-right"><strong>$<?php echo number_format($customTotal, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Total -->
    <div class="total-section">
        <h3>Estimate Summary</h3>
        <div class="total-row">
            <div class="total-label">Materials Cost:</div>
            <div class="total-value">$<?php echo number_format($estimate['materials_cost'], 2); ?></div>
        </div>
        <div class="total-row">
            <div class="total-label">Machine & Labor Cost:</div>
            <div class="total-value">$<?php echo number_format($estimate['machine_cost'] + $estimate['labor_cost'], 2); ?></div>
        </div>
        <?php if (!empty($estimate['custom_items'])): ?>
        <div class="total-row">
            <div class="total-label">Additional Items:</div>
            <div class="total-value">$<?php echo number_format($customTotal, 2); ?></div>
        </div>
        <?php endif; ?>
        <div class="total-row grand-total">
            <div class="total-label">TOTAL ESTIMATE:</div>
            <div class="total-value">$<?php echo number_format($estimate['total_estimate'], 2); ?></div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Ozark Made Crafts</strong> | Precision Craftsmanship with a Personal Touch</p>
        <p style="font-size: 10px; margin-top: 10px;">
            This estimate is valid for 30 days from the date of issue. Final pricing may vary based on material availability and project changes.
        </p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>