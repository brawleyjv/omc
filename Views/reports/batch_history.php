<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "public/login.php");
    exit();
}

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$pdo = $database->getPdo();

// Get project_id from URL
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;

// If no project selected, show project selector
if (!$project_id) {
    $projectsQuery = "
        SELECT p.id, p.project_name, COUNT(pb.id) as batch_count
        FROM projects p
        LEFT JOIN production_batches pb ON p.id = pb.project_id
        WHERE p.production_status IN ('ready', 'active')
        GROUP BY p.id, p.project_name
        HAVING batch_count > 0
        ORDER BY p.project_name
    ";
    $projectsStmt = $pdo->query($projectsQuery);
    $projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Get project details
    $projectQuery = "SELECT * FROM projects WHERE id = :id";
    $projectStmt = $pdo->prepare($projectQuery);
    $projectStmt->execute([':id' => $project_id]);
    $project = $projectStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        header("Location: " . BASE_URL . "Views/reports/batch_history.php");
        exit();
    }
    
    // Get all batches for this project
    $batchesQuery = "
        SELECT 
            pb.production_date,
            pb.batch_number,
            pb.quantity_produced,
            pb.labor_hours,
            pb.laser_time,
            pb.mill_time,
            (pb.labor_hours + (pb.laser_time / 60) + (pb.mill_time / 60)) as total_hours,
            (pb.labor_hours + (pb.laser_time / 60) + (pb.mill_time / 60)) / pb.quantity_produced as hours_per_unit,
            pb.material_cost,
            pb.labor_cost,
            (pb.material_cost + pb.labor_cost) as total_cost,
            (pb.material_cost + pb.labor_cost) / pb.quantity_produced as cost_per_unit,
            e.total_estimate as sale_price_per_unit,
            (e.total_estimate * pb.quantity_produced) as total_sale_value,
            ((e.total_estimate * pb.quantity_produced) - (pb.material_cost + pb.labor_cost)) as batch_profit,
            pb.produced_by,
            pb.notes
        FROM production_batches pb
        INNER JOIN projects p ON pb.project_id = p.id
        LEFT JOIN estimates e ON p.estimate_id = e.id
        WHERE pb.project_id = :project_id
        ORDER BY pb.production_date DESC, pb.batch_number DESC
    ";
    $batchesStmt = $pdo->prepare($batchesQuery);
    $batchesStmt->execute([':project_id' => $project_id]);
    $batches = $batchesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $totalBatches = count($batches);
    $totalProduced = array_sum(array_column($batches, 'quantity_produced'));
    $avgBatchSize = $totalBatches > 0 ? $totalProduced / $totalBatches : 0;
    
    $totalHours = array_sum(array_column($batches, 'total_hours'));
    $avgHoursPerUnit = $totalProduced > 0 ? $totalHours / $totalProduced : 0;
    
    $totalCost = array_sum(array_column($batches, 'total_cost'));
    $avgCostPerUnit = $totalProduced > 0 ? $totalCost / $totalProduced : 0;
    
    $totalSaleValue = array_sum(array_column($batches, 'total_sale_value'));
    $totalProfit = array_sum(array_column($batches, 'batch_profit'));
    $profitMargin = $totalSaleValue > 0 ? ($totalProfit / $totalSaleValue) * 100 : 0;
    
    // Get time trend data (average hours per unit over time)
    $timeTrend = [];
    foreach ($batches as $batch) {
        $timeTrend[] = [
            'date' => $batch['production_date'],
            'hours_per_unit' => $batch['hours_per_unit']
        ];
    }
    $timeTrend = array_reverse($timeTrend); // Oldest first for chart
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch History - OMC Reports</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css">

<style>
    .report-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .report-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .report-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-secondary {
        background: #64748b;
        color: white;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .project-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .project-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .project-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }
    
    .project-meta {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .summary-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .summary-card.primary { border-left: 4px solid #3b82f6; }
    .summary-card.success { border-left: 4px solid #10b981; }
    .summary-card.warning { border-left: 4px solid #f59e0b; }
    .summary-card.info { border-left: 4px solid #06b6d4; }
    
    .summary-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .summary-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .chart-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .chart-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    
    #trendChart {
        width: 100%;
        height: 300px;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .table-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table thead {
        background: #f8fafc;
    }
    
    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .data-table td {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .data-table tr:hover {
        background: #f8fafc;
    }
    
    .trend-indicator {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .trend-improving {
        background: #dcfce7;
        color: #166534;
    }
    
    .trend-stable {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .trend-declining {
        background: #fecaca;
        color: #991b1b;
    }
    
    @media print {
        .btn { display: none; }
    }
</style>

<div class="report-container">
    <!-- Header -->
    <div class="report-header">
        <div>
            <h1 class="report-title">📈 Batch History</h1>
            <?php if ($project_id): ?>
                <p style="color: #64748b; margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($project['project_name']); ?>
                </p>
            <?php else: ?>
                <p style="color: #64748b; margin-top: 0.5rem;">
                    Select a project to view batch history and performance trends
                </p>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo BASE_URL; ?>Views/reports/index.php" class="btn btn-secondary">← Back to Reports</a>
    </div>
    
    <?php if (!$project_id): ?>
        <!-- Project Selector -->
        <div class="project-grid">
            <?php if (empty($projects)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #94a3b8;">
                    <p style="font-size: 1.125rem; margin-bottom: 1rem;">No production batches found</p>
                    <p>Start recording production batches to see batch history reports.</p>
                    <a href="<?php echo BASE_URL; ?>Views/production/record_batch.php" class="btn btn-primary" style="margin-top: 1rem;">
                        Record First Batch
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $proj): ?>
                    <a href="?project_id=<?php echo $proj['id']; ?>" class="project-card">
                        <div class="project-name"><?php echo htmlspecialchars($proj['project_name']); ?></div>
                        <div class="project-meta">
                            <?php echo number_format($proj['batch_count']); ?> batches recorded
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Project Statistics -->
        <div class="summary-grid">
            <div class="summary-card primary">
                <div class="summary-label">Total Batches</div>
                <div class="summary-value"><?php echo number_format($totalBatches); ?></div>
            </div>
            
            <div class="summary-card success">
                <div class="summary-label">Total Units Produced</div>
                <div class="summary-value"><?php echo number_format($totalProduced ?? 0); ?></div>
            </div>
            
            <div class="summary-card info">
                <div class="summary-label">Avg Batch Size</div>
                <div class="summary-value"><?php echo number_format($avgBatchSize ?? 0, 1); ?></div>
            </div>
            
            <div class="summary-card warning">
                <div class="summary-label">Avg Time/Unit</div>
                <div class="summary-value"><?php echo number_format($avgHoursPerUnit ?? 0, 2); ?> hrs</div>
            </div>
            
            <div class="summary-card danger">
                <div class="summary-label">Total Production Cost</div>
                <div class="summary-value">$<?php echo number_format($totalCost ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card success">
                <div class="summary-label">Total Sale Value</div>
                <div class="summary-value">$<?php echo number_format($totalSaleValue ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card primary">
                <div class="summary-label">Total Profit</div>
                <div class="summary-value">$<?php echo number_format($totalProfit ?? 0, 2); ?></div>
            </div>
            
            <div class="summary-card <?php echo ($profitMargin ?? 0) >= 40 ? 'success' : (($profitMargin ?? 0) >= 25 ? 'info' : 'warning'); ?>">
                <div class="summary-label">Profit Margin</div>
                <div class="summary-value"><?php echo number_format($profitMargin ?? 0, 1); ?>%</div>
            </div>
        </div>
        
        <!-- Time Trend Chart -->
        <div class="chart-card">
            <h3 class="chart-title">⚡ Efficiency Trend (Hours per Unit)</h3>
            <canvas id="trendChart"></canvas>
            <p style="color: #64748b; font-size: 0.875rem; margin-top: 1rem;">
                Lower is better. Shows how production time per unit changes over batches.
            </p>
        </div>
        
        <!-- Export Buttons -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
        </div>
        
        <!-- Batch History Table -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Batch Details</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Batch #</th>
                        <th>Quantity</th>
                        <th>Labor (hrs)</th>
                        <th>Laser (min)</th>
                        <th>Mill (min)</th>
                        <th>Total Time</th>
                        <th>Time/Unit</th>
                        <th>Total Cost</th>
                        <th>Cost/Unit</th>
                        <th>Sale Value</th>
                        <th>Profit</th>
                        <th>Produced By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="13" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                No batches recorded yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $prevHoursPerUnit = null;
                        foreach ($batches as $batch): 
                            // Determine trend
                            $trend = 'stable';
                            if ($prevHoursPerUnit !== null) {
                                $change = (($batch['hours_per_unit'] - $prevHoursPerUnit) / $prevHoursPerUnit) * 100;
                                if ($change < -10) {
                                    $trend = 'improving';
                                } elseif ($change > 10) {
                                    $trend = 'declining';
                                }
                            }
                            $prevHoursPerUnit = $batch['hours_per_unit'];
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($batch['production_date'])); ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($batch['batch_number']); ?></td>
                                <td><?php echo number_format($batch['quantity_produced'] ?? 0); ?></td>
                                <td><?php echo number_format($batch['labor_hours'] ?? 0, 2); ?></td>
                                <td><?php echo number_format($batch['laser_time'] ?? 0); ?></td>
                                <td><?php echo number_format($batch['mill_time'] ?? 0); ?></td>
                                <td><?php echo number_format($batch['total_hours'] ?? 0, 2); ?> hrs</td>
                                <td>
                                    <?php echo number_format($batch['hours_per_unit'] ?? 0, 3); ?> hrs
                                    <?php if ($trend !== 'stable'): ?>
                                        <span class="trend-indicator trend-<?php echo $trend; ?>">
                                            <?php echo $trend === 'improving' ? '↓' : '↑'; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #dc2626;">$<?php echo number_format($batch['total_cost'] ?? 0, 2); ?></td>
                                <td>$<?php echo number_format($batch['cost_per_unit'] ?? 0, 2); ?></td>
                                <td style="color: #059669; font-weight: 600;">$<?php echo number_format($batch['total_sale_value'] ?? 0, 2); ?></td>
                                <td style="color: <?php echo ($batch['batch_profit'] ?? 0) > 0 ? '#059669' : '#dc2626'; ?>; font-weight: 600;">
                                    $<?php echo number_format($batch['batch_profit'] ?? 0, 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($batch['produced_by'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if ($project_id && !empty($timeTrend)): ?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Time Trend Chart
const trendData = <?php echo json_encode($timeTrend); ?>;
const ctx = document.getElementById('trendChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: trendData.map(d => d.date),
        datasets: [{
            label: 'Hours per Unit',
            data: trendData.map(d => parseFloat(d.hours_per_unit)),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Time: ' + context.parsed.y.toFixed(3) + ' hrs/unit';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                title: {
                    display: true,
                    text: 'Hours per Unit'
                },
                ticks: {
                    callback: function(value) {
                        return value.toFixed(3);
                    }
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Production Date'
                }
            }
        }
    }
});
</script>
<?php endif; ?>

</body>
</html>
