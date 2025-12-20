<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';
require_once BASE_PATH . '/Models/Settings.php';

use MyApp\Models\Database;
use Models\Settings;

// Get company settings
$settingsModel = new Settings();
$companySettings = $settingsModel->getSettings();

$company_name = $companySettings['company_name'] ?? 'Your Company Name';
$company_slogan = $companySettings['company_slogan'] ?? '';
$company_address = $companySettings['company_address'] ?? '';
$company_city = $companySettings['company_city'] ?? '';
$company_state = $companySettings['company_state'] ?? '';
$company_zip = $companySettings['company_zip'] ?? '';
$company_phone = $companySettings['company_phone'] ?? '';
$company_email = $companySettings['company_email'] ?? '';
$company_logo = $companySettings['company_logo'] ?? '';

// Format address
$full_address = '';
if (!empty($company_address)) {
    $full_address = $company_address;
    if (!empty($company_city) || !empty($company_state) || !empty($company_zip)) {
        $full_address .= ', ';
        if (!empty($company_city)) $full_address .= $company_city . ', ';
        if (!empty($company_state)) $full_address .= $company_state . ' ';
        if (!empty($company_zip)) $full_address .= $company_zip;
    }
}

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
        $ratesQuery = "SELECT mill_rate, laser_rate, labor_rate FROM setup LIMIT 1";
        $ratesStmt = $conn->prepare($ratesQuery);
        $ratesStmt->execute();
        $rates = $ratesStmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate individual machine costs
        $routerCost = $estimate['router_time'] * ($rates['mill_rate'] ?? 0.85);
        $laserCost = $estimate['laser_time'] * ($rates['laser_rate'] ?? 0.50);
        
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
        @page { 
            margin: 10mm 15mm; 
            size: letter;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #000;
            line-height: 1.3;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
        }
        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: middle;
            text-align: left;
        }
        .header-center {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
            text-align: center;
        }
        .header-right {
            display: table-cell;
            width: 30%;
            vertical-align: middle;
            text-align: right;
            font-size: 7pt;
            line-height: 1.4;
        }
        .company-logo {
            max-width: 120px;
            max-height: 60px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .company-slogan {
            font-size: 8pt;
            color: #333;
            font-style: italic;
            margin-bottom: 4px;
        }
        .company-info {
            color: #000;
        }
        .estimate-title {
            font-size: 12pt;
            color: #000;
            margin: 3px 0;
            font-weight: bold;
        }
        .estimate-number {
            font-size: 10pt;
            font-weight: bold;
            color: #000;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 3px;
        }
        .info-box {
            background: #f5f5f5;
            padding: 6px 8px;
            border: 1px solid #000;
        }
        .info-label {
            font-weight: bold;
            color: #000;
            font-size: 9pt;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-box div {
            margin: 1px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8pt;
        }
        th {
            background: #000;
            color: white;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 8pt;
        }
        td {
            padding: 3px 6px;
            border: 1px solid #ccc;
            font-size: 8pt;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            background: #f0f0f0;
            padding: 8px;
            border: 2px solid #000;
            margin-top: 8px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin: 2px 0;
            font-size: 8pt;
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
            font-size: 11pt;
            color: #000;
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7pt;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .notes {
            background: #f9f9f9;
            padding: 6px 8px;
            border-left: 3px solid #000;
            margin: 8px 0;
            font-size: 8pt;
        }
        h3 {
            font-size: 9pt;
            margin: 8px 0 4px 0;
            color: #000;
            font-weight: bold;
            text-transform: uppercase;
        }
        strong {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <?php if (!empty($company_logo) && file_exists(BASE_PATH . $company_logo)): ?>
                <img src="<?php echo BASE_URL . $company_logo; ?>" alt="Company Logo" class="company-logo">
            <?php endif; ?>
        </div>
        <div class="header-center">
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <?php if (!empty($company_slogan)): ?>
                <div class="company-slogan"><?php echo htmlspecialchars($company_slogan); ?></div>
            <?php endif; ?>
            <div class="estimate-title">PROJECT ESTIMATE</div>
            <div class="estimate-number"><?php echo htmlspecialchars($estimate['estimate_number']); ?></div>
        </div>
        <div class="header-right">
            <div class="company-info">
                <?php if (!empty($full_address)): ?>
                    <div><?php echo htmlspecialchars($full_address); ?></div>
                <?php endif; ?>
                <?php if (!empty($company_phone)): ?>
                    <div>Phone: <?php echo htmlspecialchars($company_phone); ?></div>
                <?php endif; ?>
                <?php if (!empty($company_email)): ?>
                    <div>Email: <?php echo htmlspecialchars($company_email); ?></div>
                <?php endif; ?>
            </div>
        </div>
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
    <?php 
    // Calculate materials with markup applied
    $materialsCostWithMarkup = $estimate['materials_cost'] / 0.3;
    ?>
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
            <?php 
            // Calculate markup ratio
            $materialMarkupRatio = $estimate['materials_cost'] > 0 ? $materialsCostWithMarkup / $estimate['materials_cost'] : 1;
            foreach ($estimate['materials'] as $material): 
                $markedUpCost = $material['total_cost'] * $materialMarkupRatio;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($material['material_name']); ?></td>
                <td><?php echo number_format($material['quantity'], 2); ?></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $material['unit_type'])); ?></td>
                <td>$<?php echo number_format($material['unit_cost'], 2); ?></td>
                <td class="text-right">$<?php echo number_format($markedUpCost, 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="text-right"><strong>Materials Subtotal:</strong></td>
                <td class="text-right"><strong>$<?php echo number_format($materialsCostWithMarkup, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Labor & Machine Time -->
    <?php
    // Machine costs are at the rates set in settings (NO markup)
    // Only manual labor gets markup
    $laborCostWithMarkup = ($estimate['labor_hours'] * ($rates['labor_rate'] ?? 25.00)) / 0.2;
    ?>
    <h3>Labor & Machine Time</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Time/Quantity</th>
                <th class="text-right">Cost</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CNC Router Time</td>
                <td><?php echo number_format($estimate['router_time'], 2); ?> minutes</td>
                <td class="text-right">$<?php echo number_format($routerCost, 2); ?></td>
            </tr>
            <tr>
                <td>Laser Time</td>
                <td><?php echo number_format($estimate['laser_time'], 2); ?> minutes</td>
                <td class="text-right">$<?php echo number_format($laserCost, 2); ?></td>
            </tr>
            <tr>
                <td>Manual Labor</td>
                <td><?php echo number_format($estimate['labor_hours'], 2); ?> hours</td>
                <td class="text-right">$<?php echo number_format($laborCostWithMarkup, 2); ?></td>
            </tr>
            <?php 
            $bitChanges = $estimate['bit_changes'] ?? 0;
            $needsCustomization = $estimate['needs_customization'] ?? 0;
            $shippingCost = $estimate['shipping_cost'] ?? 0;
            
            $ratesQuery2 = "SELECT bit_change_rate, customize_rate FROM setup LIMIT 1";
            $ratesStmt2 = $conn->prepare($ratesQuery2);
            $ratesStmt2->execute();
            $rates2 = $ratesStmt2->fetch(PDO::FETCH_ASSOC);
            
            $bitChangeCost = $bitChanges * ($rates2['bit_change_rate'] ?? 5.00);
            $customizationCost = $needsCustomization ? ($rates2['customize_rate'] ?? 5.00) : 0;
            
            if ($bitChangeCost > 0): ?>
            <tr>
                <td>Bit Changes</td>
                <td><?php echo $bitChanges; ?> changes</td>
                <td class="text-right">$<?php echo number_format($bitChangeCost, 2); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($customizationCost > 0): ?>
            <tr>
                <td>Customization</td>
                <td>Yes</td>
                <td class="text-right">$<?php echo number_format($customizationCost, 2); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($shippingCost > 0): ?>
            <tr>
                <td>Shipping/Packaging</td>
                <td>-</td>
                <td class="text-right">$<?php echo number_format($shippingCost, 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="text-right"><strong>Labor & Services Subtotal:</strong></td>
                <td class="text-right"><strong>$<?php echo number_format($routerCost + $laserCost + $laborCostWithMarkup + $bitChangeCost + $customizationCost + $shippingCost, 2); ?></strong></td>
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
            <div class="total-label">Materials Subtotal:</div>
            <div class="total-value">$<?php echo number_format($materialsCostWithMarkup, 2); ?></div>
        </div>
        <div class="total-row">
            <div class="total-label">Labor & Services Subtotal:</div>
            <div class="total-value">$<?php echo number_format($routerCost + $laserCost + $laborCostWithMarkup + $bitChangeCost + $customizationCost + $shippingCost, 2); ?></div>
        </div>
        <?php if (!empty($estimate['custom_items']) && $customTotal > 0): ?>
        <div class="total-row">
            <div class="total-label">Additional Items Subtotal:</div>
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
        <strong>Ozark Made Crafts</strong> | Precision Craftsmanship with a Personal Touch<br>
        <span style="font-size: 7pt; margin-top: 3px; display: block;">
            This estimate is valid for 30 days from the date of issue.
        </span>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>