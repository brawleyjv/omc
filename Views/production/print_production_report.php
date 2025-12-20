<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/ProductionModel.php';
require_once BASE_PATH . '/Models/Settings.php';

use MyApp\Models\Database;
use MyApp\Models\ProductionModel;
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

// Get date range from query parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Default to today
$projectId = $_GET['project_id'] ?? null; // Optional filter by project

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $pdo = $database->getPdo();
    
    // Build query
    $sql = "SELECT pb.*, p.project_name, p.id as project_id_ref
            FROM production_batches pb
            JOIN projects p ON pb.project_id = p.id
            WHERE pb.production_date BETWEEN :start_date AND :end_date";
    
    $params = [
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ];
    
    if ($projectId) {
        $sql .= " AND pb.project_id = :project_id";
        $params[':project_id'] = $projectId;
    }
    
    $sql .= " ORDER BY pb.production_date DESC, pb.batch_number DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalQuantity = 0;
    $totalLaborHours = 0;
    $totalLaserMins = 0;
    $totalMillMins = 0;
    $totalMaterialCost = 0;
    $totalLaborCost = 0;
    
    foreach ($batches as $batch) {
        $totalQuantity += $batch['quantity_produced'];
        $totalLaborHours += $batch['labor_hours'] ?? 0;
        $totalLaserMins += $batch['laser_time'] ?? 0;
        $totalMillMins += $batch['mill_time'] ?? 0;
        $totalMaterialCost += $batch['material_cost'] ?? 0;
        $totalLaborCost += $batch['labor_cost'] ?? 0;
    }
    
} catch (Exception $e) {
    error_log("Error loading production data: " . $e->getMessage());
    die("Error loading production report: " . $e->getMessage() . "<br><br>Query: " . ($sql ?? 'N/A'));
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Production Report - <?php echo date('M d, Y', strtotime($startDate)); ?> to <?php echo date('M d, Y', strtotime($endDate)); ?></title>
    <style>
        @page { 
            margin: 10mm 15mm; 
            size: letter landscape;
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
            padding: 15px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .company-slogan {
            font-size: 9pt;
            color: #555;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 8pt;
            line-height: 1.4;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .report-subtitle {
            font-size: 10pt;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8pt;
        }
        th {
            background-color: #333;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        td {
            padding: 5px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-row {
            background-color: #e8e8e8 !important;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .summary-box {
            margin-top: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 2px solid #333;
        }
        .summary-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 8pt;
            color: #555;
        }
        .summary-value {
            font-size: 14pt;
            font-weight: bold;
        }
        .no-print {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
        }
        .date-filter {
            margin: 10px 0;
            padding: 15px;
            background-color: #f0f9ff;
            border: 1px solid #3b82f6;
            border-radius: 4px;
        }
        .date-filter h4 {
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        .date-filter form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .date-filter .form-group {
            display: flex;
            flex-direction: column;
        }
        .date-filter label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 4px;
            color: #374151;
        }
        .date-filter input,
        .date-filter select {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .date-filter button {
            padding: 7px 16px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        .date-filter button:hover {
            background-color: #2563eb;
        }
        .quick-filters {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        .quick-filters button {
            padding: 4px 10px;
            background-color: white;
            color: #3b82f6;
            border: 1px solid #3b82f6;
            font-size: 0.75rem;
        }
        .quick-filters button:hover {
            background-color: #eff6ff;
        }
        @media print {
            .no-print, .date-filter {
                display: none;
            }
            body {
                padding: 0;
            }
        }
        .notes {
            font-size: 7pt;
            color: #666;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <!-- Print Controls (hidden when printing) -->
    <div class="no-print">
        <button onclick="window.print()" style="padding: 8px 16px; font-size: 11pt; cursor: pointer;">🖨️ Print Report</button>
        <button onclick="exportCSV()" style="padding: 8px 16px; font-size: 11pt; cursor: pointer; margin-left: 10px; background-color: #10b981; color: white; border: none; border-radius: 4px;">📊 Export CSV</button>
        <a href="<?php echo BASE_URL; ?>Views/reports/index.php" style="padding: 8px 16px; font-size: 11pt; cursor: pointer; margin-left: 10px; text-decoration: none; background: #fff; border: 1px solid #ddd; border-radius: 4px; display: inline-block;">✖ Close</a>
    </div>

    <!-- Date Filter -->
    <div class="date-filter">
        <h4>📅 Filter Production Report</h4>
        <form method="GET">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
            </div>
            <button type="submit">Apply Filter</button>
        </form>
        <div class="quick-filters">
            <strong style="font-size: 0.875rem; margin-right: 10px;">Quick Filters:</strong>
            <button onclick="setDateRange('today')">Today</button>
            <button onclick="setDateRange('week')">This Week</button>
            <button onclick="setDateRange('month')">This Month</button>
            <button onclick="setDateRange('year')">This Year</button>
            <button onclick="setDateRange('all')">All Time</button>
        </div>
    </div>

    <script>
        function exportCSV() {
            const params = new URLSearchParams(window.location.search);
            const startDate = params.get('start_date') || '<?php echo $startDate; ?>';
            const endDate = params.get('end_date') || '<?php echo $endDate; ?>';
            window.location.href = `<?php echo BASE_URL; ?>Views/production/export_production_csv.php?start_date=${startDate}&end_date=${endDate}`;
        }
        
        function setDateRange(range) {
            const today = new Date();
            let startDate, endDate;
            
            switch(range) {
                case 'today':
                    startDate = endDate = formatDate(today);
                    break;
                case 'week':
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    startDate = formatDate(weekStart);
                    endDate = formatDate(today);
                    break;
                case 'month':
                    startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                    endDate = formatDate(today);
                    break;
                case 'year':
                    startDate = formatDate(new Date(today.getFullYear(), 0, 1));
                    endDate = formatDate(today);
                    break;
                case 'all':
                    startDate = '2020-01-01';
                    endDate = formatDate(today);
                    break;
            }
            
            window.location.href = `?start_date=${startDate}&end_date=${endDate}`;
        }
        
        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }
    </script>

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
            <?php if ($company_slogan): ?>
                <div class="company-slogan"><?php echo htmlspecialchars($company_slogan); ?></div>
            <?php endif; ?>
            <div class="company-info">
                <?php if ($full_address): ?>
                    <?php echo htmlspecialchars($full_address); ?><br>
                <?php endif; ?>
                <?php if ($company_phone): ?>
                    <?php echo htmlspecialchars($company_phone); ?>
                <?php endif; ?>
                <?php if ($company_email): ?>
                    <?php if ($company_phone) echo ' • '; ?>
                    <?php echo htmlspecialchars($company_email); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-right">
            <div class="report-title">PRODUCTION REPORT</div>
            <div class="report-subtitle">
                <?php echo date('M d, Y', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($endDate)); ?>
            </div>
            <div style="margin-top: 5px; font-size: 8pt;">
                <strong>Generated:</strong> <?php echo date('M d, Y g:i A'); ?>
            </div>
        </div>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-title">Production Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Batches</div>
                <div class="summary-value"><?php echo count($batches); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Units</div>
                <div class="summary-value"><?php echo number_format($totalQuantity); ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Labor</div>
                <div class="summary-value"><?php echo number_format($totalLaborHours, 2); ?> hrs</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Machine Time</div>
                <div class="summary-value"><?php echo number_format(($totalLaserMins + $totalMillMins) / 60, 2); ?> hrs</div>
            </div>
        </div>
    </div>

    <!-- Production Batches Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Date</th>
                <th style="width: 10%;">Batch #</th>
                <th style="width: 18%;">Project</th>
                <th style="width: 6%;" class="text-center">Qty</th>
                <th style="width: 7%;" class="text-right">Labor<br>(hrs)</th>
                <th style="width: 7%;" class="text-right">Laser<br>(mins)</th>
                <th style="width: 7%;" class="text-right">Mill<br>(mins)</th>
                <th style="width: 7%;" class="text-right">Total Time<br>(hrs)</th>
                <th style="width: 7%;" class="text-right">Time/Unit<br>(hrs)</th>
                <th style="width: 12%;">Produced By</th>
                <th style="width: 11%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($batches)): ?>
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #999;">
                        No production batches found for this date range.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($batches as $batch): ?>
                    <?php
                    $totalTime = ($batch['labor_hours'] ?? 0) + (($batch['laser_time'] ?? 0) + ($batch['mill_time'] ?? 0)) / 60;
                    $timePerUnit = $batch['quantity_produced'] > 0 ? $totalTime / $batch['quantity_produced'] : 0;
                    ?>
                    <tr>
                        <td><?php echo date('m/d/Y', strtotime($batch['production_date'])); ?></td>
                        <td><?php echo htmlspecialchars($batch['batch_number'] ?? '-'); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($batch['project_name']); ?></strong><br>
                            <span style="font-size: 7pt; color: #666;">Project ID: <?php echo htmlspecialchars($batch['project_id']); ?></span>
                        </td>
                        <td class="text-center"><?php echo number_format($batch['quantity_produced']); ?></td>
                        <td class="text-right"><?php echo number_format($batch['labor_hours'] ?? 0, 2); ?></td>
                        <td class="text-right"><?php echo number_format($batch['laser_time'] ?? 0, 0); ?></td>
                        <td class="text-right"><?php echo number_format($batch['mill_time'] ?? 0, 0); ?></td>
                        <td class="text-right"><?php echo number_format($totalTime, 2); ?></td>
                        <td class="text-right"><?php echo number_format($timePerUnit, 4); ?></td>
                        <td><?php echo htmlspecialchars($batch['produced_by'] ?? '-'); ?></td>
                        <td class="notes" title="<?php echo htmlspecialchars($batch['notes'] ?? ''); ?>">
                            <?php echo htmlspecialchars($batch['notes'] ?? '-'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Totals Row -->
                <tr class="totals-row">
                    <td colspan="3" class="text-right">TOTALS:</td>
                    <td class="text-center"><?php echo number_format($totalQuantity); ?></td>
                    <td class="text-right"><?php echo number_format($totalLaborHours, 2); ?></td>
                    <td class="text-right"><?php echo number_format($totalLaserMins, 0); ?></td>
                    <td class="text-right"><?php echo number_format($totalMillMins, 0); ?></td>
                    <td class="text-right"><?php echo number_format($totalLaborHours + ($totalLaserMins + $totalMillMins) / 60, 2); ?></td>
                    <td colspan="3"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 7pt; color: #999; text-align: center;">
        Production Report • <?php echo htmlspecialchars($company_name); ?> • Page 1
    </div>
</body>
</html>
