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

// Get efficiency metrics by project
$metricsQuery = "
    SELECT 
        p.id,
        p.project_name,
        p.production_status,
        COUNT(pb.id) as batch_count,
        SUM(pb.quantity_produced) as total_produced,
        AVG(pb.quantity_produced) as avg_batch_size,
        
        -- Time metrics
        SUM(pb.labor_hours + (pb.laser_time / 60) + (pb.mill_time / 60)) as total_hours,
        AVG(pb.labor_hours + (pb.laser_time / 60) + (pb.mill_time / 60)) / NULLIF(AVG(pb.quantity_produced), 0) as avg_hours_per_unit,
        
        -- First and last batch times per unit for trend
        (
            SELECT (labor_hours + (laser_time / 60) + (mill_time / 60)) / quantity_produced
            FROM production_batches
            WHERE project_id = p.id
            ORDER BY production_date ASC
            LIMIT 1
        ) as first_batch_time_per_unit,
        (
            SELECT (labor_hours + (laser_time / 60) + (mill_time / 60)) / quantity_produced
            FROM production_batches
            WHERE project_id = p.id
            ORDER BY production_date DESC
            LIMIT 1
        ) as latest_batch_time_per_unit,
        
        -- Cost metrics
        AVG((pb.material_cost + pb.labor_cost) / pb.quantity_produced) as avg_cost_per_unit,
        
        -- Dates
        MIN(pb.production_date) as first_production,
        MAX(pb.production_date) as last_production,
        DATEDIFF(MAX(pb.production_date), MIN(pb.production_date)) as days_producing
        
    FROM projects p
    INNER JOIN production_batches pb ON p.id = pb.project_id
    WHERE p.production_status IN ('ready', 'active')
    GROUP BY p.id, p.project_name, p.production_status
    HAVING batch_count >= 2
    ORDER BY total_produced DESC
";

$metricsStmt = $pdo->query($metricsQuery);
$metrics = $metricsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate efficiency trends and rankings
foreach ($metrics as &$row) {
    // Calculate improvement percentage
    if ($row['first_batch_time_per_unit'] && $row['latest_batch_time_per_unit']) {
        $improvement = (($row['first_batch_time_per_unit'] - $row['latest_batch_time_per_unit']) / $row['first_batch_time_per_unit']) * 100;
        $row['improvement_pct'] = $improvement;
        $row['is_improving'] = $improvement > 5;
        $row['is_declining'] = $improvement < -5;
    } else {
        $row['improvement_pct'] = 0;
        $row['is_improving'] = false;
        $row['is_declining'] = false;
    }
    
    // Calculate production rate (units per day)
    $row['units_per_day'] = $row['days_producing'] > 0 ? $row['total_produced'] / $row['days_producing'] : 0;
}
unset($row);

// Sort by efficiency (lowest time per unit = best)
$bestPerformers = $metrics;
usort($bestPerformers, function($a, $b) {
    return $a['avg_hours_per_unit'] <=> $b['avg_hours_per_unit'];
});
$bestPerformers = array_slice($bestPerformers, 0, 5);

// Most improved (highest improvement percentage)
$mostImproved = array_filter($metrics, function($row) {
    return $row['is_improving'];
});
usort($mostImproved, function($a, $b) {
    return $b['improvement_pct'] <=> $a['improvement_pct'];
});
$mostImproved = array_slice($mostImproved, 0, 5);

// Calculate overall stats
$totalProjects = count($metrics);
$improvingCount = count(array_filter($metrics, function($row) { return $row['is_improving']; }));
$decliningCount = count(array_filter($metrics, function($row) { return $row['is_declining']; }));
$avgImprovement = array_sum(array_column($metrics, 'improvement_pct')) / max(count($metrics), 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Efficiency Metrics - OMC Reports</title>
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
    .summary-card.danger { border-left: 4px solid #ef4444; }
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
    
    .summary-subtext {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }
    
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
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
    
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-success {
        background: #dcfce7;
        color: #166534;
    }
    
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-danger {
        background: #fecaca;
        color: #991b1b;
    }
    
    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .rank-medal {
        display: inline-block;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        font-weight: 700;
        font-size: 0.75rem;
    }
    
    .rank-1 {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
    }
    
    .rank-2 {
        background: linear-gradient(135deg, #e5e7eb, #9ca3af);
        color: white;
    }
    
    .rank-3 {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }
    
    @media print {
        .btn { display: none; }
    }
</style>

<div class="report-container">
    <!-- Header -->
    <div class="report-header">
        <div>
            <h1 class="report-title">⚡ Production Efficiency Metrics</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">
                Performance trends and optimization insights
            </p>
        </div>
        
        <a href="<?php echo BASE_URL; ?>Views/reports/index.php" class="btn btn-secondary">← Back to Reports</a>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card primary">
            <div class="summary-label">Projects Tracked</div>
            <div class="summary-value"><?php echo number_format($totalProjects); ?></div>
            <div class="summary-subtext">With 2+ batches</div>
        </div>
        
        <div class="summary-card success">
            <div class="summary-label">Improving Efficiency</div>
            <div class="summary-value"><?php echo number_format($improvingCount); ?></div>
            <div class="summary-subtext"><?php echo $totalProjects > 0 ? number_format(($improvingCount / $totalProjects) * 100, 1) : 0; ?>% of projects</div>
        </div>
        
        <div class="summary-card danger">
            <div class="summary-label">Declining Efficiency</div>
            <div class="summary-value"><?php echo number_format($decliningCount); ?></div>
            <div class="summary-subtext"><?php echo $totalProjects > 0 ? number_format(($decliningCount / $totalProjects) * 100, 1) : 0; ?>% of projects</div>
        </div>
        
        <div class="summary-card <?php echo $avgImprovement >= 0 ? 'success' : 'warning'; ?>">
            <div class="summary-label">Avg Improvement</div>
            <div class="summary-value"><?php echo $avgImprovement >= 0 ? '+' : ''; ?><?php echo number_format($avgImprovement, 1); ?>%</div>
            <div class="summary-subtext">Time per unit change</div>
        </div>
    </div>
    
    <!-- Export Button -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
    </div>
    
    <!-- Best Performers -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">🏆 Best Performers (Fastest Production)</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product</th>
                    <th>Batches</th>
                    <th>Total Produced</th>
                    <th>Avg Time/Unit</th>
                    <th>Avg Cost/Unit</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bestPerformers)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No efficiency data available yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bestPerformers as $index => $row): 
                        $rank = $index + 1;
                    ?>
                        <tr>
                            <td>
                                <?php if ($rank <= 3): ?>
                                    <span class="rank-medal rank-<?php echo $rank; ?>"><?php echo $rank; ?></span>
                                <?php else: ?>
                                    #<?php echo $rank; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['project_name']); ?></strong>
                            </td>
                            <td><?php echo number_format($row['batch_count']); ?></td>
                            <td><?php echo number_format($row['total_produced']); ?></td>
                            <td style="font-weight: 600; color: #10b981;">
                                <?php echo number_format($row['avg_hours_per_unit'], 3); ?> hrs
                            </td>
                            <td>$<?php echo number_format($row['avg_cost_per_unit'], 2); ?></td>
                            <td>
                                <?php if ($row['is_improving']): ?>
                                    <span class="badge badge-success">
                                        ↓ <?php echo number_format(abs($row['improvement_pct']), 1); ?>% Faster
                                    </span>
                                <?php elseif ($row['is_declining']): ?>
                                    <span class="badge badge-danger">
                                        ↑ <?php echo number_format(abs($row['improvement_pct']), 1); ?>% Slower
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-info">→ Stable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Most Improved -->
    <?php if (!empty($mostImproved)): ?>
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">📈 Most Improved (Biggest Time Savings)</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batches</th>
                        <th>First Batch Time/Unit</th>
                        <th>Latest Batch Time/Unit</th>
                        <th>Improvement</th>
                        <th>Time Saved/Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mostImproved as $row): 
                        $timeSaved = $row['first_batch_time_per_unit'] - $row['latest_batch_time_per_unit'];
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['project_name']); ?></strong>
                            </td>
                            <td><?php echo number_format($row['batch_count']); ?></td>
                            <td><?php echo number_format($row['first_batch_time_per_unit'], 3); ?> hrs</td>
                            <td style="font-weight: 600; color: #10b981;">
                                <?php echo number_format($row['latest_batch_time_per_unit'], 3); ?> hrs
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    ↓ <?php echo number_format($row['improvement_pct'], 1); ?>%
                                </span>
                            </td>
                            <td style="color: #10b981; font-weight: 600;">
                                -<?php echo number_format($timeSaved, 3); ?> hrs
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- All Projects -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">All Projects - Efficiency Overview</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Batches</th>
                    <th>Total Produced</th>
                    <th>Avg Time/Unit</th>
                    <th>Units/Day</th>
                    <th>Efficiency Trend</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($metrics)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            No production data available. Need at least 2 batches per project for efficiency analysis.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($metrics as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['project_name']); ?></strong>
                            </td>
                            <td>
                                <span style="text-transform: capitalize; color: #64748b;">
                                    <?php echo $row['production_status']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($row['batch_count']); ?></td>
                            <td><?php echo number_format($row['total_produced']); ?></td>
                            <td><?php echo number_format($row['avg_hours_per_unit'], 3); ?> hrs</td>
                            <td><?php echo number_format($row['units_per_day'], 1); ?></td>
                            <td>
                                <?php if ($row['is_improving']): ?>
                                    <span class="badge badge-success">
                                        ↓ Improving <?php echo number_format(abs($row['improvement_pct']), 1); ?>%
                                    </span>
                                <?php elseif ($row['is_declining']): ?>
                                    <span class="badge badge-danger">
                                        ↑ Declining <?php echo number_format(abs($row['improvement_pct']), 1); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-info">→ Stable</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>Views/reports/batch_history.php?project_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
