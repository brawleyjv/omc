<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';

use MyApp\Models\Database;

$success = false;
$error = null;
$rates = [];

// Get current rates
try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    
    $query = "SELECT * FROM setup LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rates = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rates) {
        // If no rates exist, create default entry
        $insertQuery = "INSERT INTO setup (mill_rate, laser_rate, labor_rate, bit_change_rate, customize_rate, overhead_rate, sqf_milling_rate, packaging_rate) 
                       VALUES (0.85, 0.50, 25.00, 5.00, 5.00, 10.00, 32.00, 20.00)";
        $conn->exec($insertQuery);
        
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    error_log("Error loading rates: " . $e->getMessage());
    $error = "Error loading rates: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rates'])) {
    try {
        $updateQuery = "UPDATE setup SET 
            mill_rate = :mill_rate,
            laser_rate = :laser_rate,
            labor_rate = :labor_rate,
            bit_change_rate = :bit_change_rate,
            customize_rate = :customize_rate,
            overhead_rate = :overhead_rate,
            sqf_milling_rate = :sqf_milling_rate,
            packaging_rate = :packaging_rate
            WHERE id = :id";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([
            ':mill_rate' => floatval($_POST['mill_rate']),
            ':laser_rate' => floatval($_POST['laser_rate']),
            ':labor_rate' => floatval($_POST['labor_rate']),
            ':bit_change_rate' => floatval($_POST['bit_change_rate']),
            ':customize_rate' => floatval($_POST['customize_rate']),
            ':overhead_rate' => floatval($_POST['overhead_rate']),
            ':sqf_milling_rate' => floatval($_POST['sqf_milling_rate']),
            ':packaging_rate' => floatval($_POST['packaging_rate']),
            ':id' => $rates['id']
        ]);
        
        $success = true;
        
        // Reload rates
        $stmt->execute();
        $rates = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error updating rates: " . $e->getMessage());
        $error = "Error updating rates: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Settings - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        .rate-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .rate-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }
        .rate-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .rate-unit {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-top: 0.25rem;
        }
        .info-box {
            background: #e7f3ff;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #0066cc;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Rate Settings</h1>
                    <p>Configure pricing rates for estimates and projects</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="nav-link">Estimates</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        
        <?php if ($success): ?>
            <div class="notification notification-success">
                ✅ Rates updated successfully!
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notification notification-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Current Rates Display -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title">Current Rates</h2>
                <p class="card-subtitle">These rates are used in estimate calculations</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rate-card">
                        <div class="rate-label">CNC Router/Mill</div>
                        <div class="rate-value">$<?php echo number_format($rates['mill_rate'], 2); ?></div>
                        <div class="rate-unit">per minute</div>
                    </div>
                    <div class="rate-card">
                        <div class="rate-label">Laser</div>
                        <div class="rate-value">$<?php echo number_format($rates['laser_rate'], 2); ?></div>
                        <div class="rate-unit">per minute</div>
                    </div>
                    <div class="rate-card">
                        <div class="rate-label">Labor</div>
                        <div class="rate-value">$<?php echo number_format($rates['labor_rate'], 2); ?></div>
                        <div class="rate-unit">per hour</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="info-box">
            <strong>💡 How These Rates Work:</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                <li><strong>Mill/Router Rate:</strong> Cost per minute of CNC router/mill operation</li>
                <li><strong>Laser Rate:</strong> Cost per minute of laser cutting/engraving</li>
                <li><strong>Labor Rate:</strong> Cost per hour of manual labor</li>
            </ul>
            <p style="margin-top: 0.5rem; margin-bottom: 0;">These rates are used in the estimate formula: <code>(materials_cost / 0.3) + ((labor_hours × rate) / 0.2) + machine_cost</code></p>
            <p style="margin-top: 0.25rem; color: #666; font-size: 0.9rem;">Note: Machine time (router/laser) has no markup and is charged at cost</p>
        </div>

        <!-- Update Rates Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Rates</h2>
                <p class="card-subtitle">Modify the rates below and save changes</p>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    
                    <!-- Primary Rates -->
                    <div class="form-section">
                        <h3 class="form-section-title">Primary Rates (Used in Estimates)</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="mill_rate" class="form-label">
                                    CNC Router/Mill Rate (per minute) *
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="mill_rate" name="mill_rate" 
                                           class="form-control" value="<?php echo $rates['mill_rate']; ?>" required>
                                </div>
                                <small class="form-text">Cost per minute of CNC router operation</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="laser_rate" class="form-label">
                                    Laser Rate (per minute) *
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="laser_rate" name="laser_rate" 
                                           class="form-control" value="<?php echo $rates['laser_rate']; ?>" required>
                                </div>
                                <small class="form-text">Cost per minute of laser operation</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="labor_rate" class="form-label">
                                    Labor Rate (per hour) *
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="labor_rate" name="labor_rate" 
                                           class="form-control" value="<?php echo $rates['labor_rate']; ?>" required>
                                </div>
                                <small class="form-text">Cost per hour of manual labor</small>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Rates -->
                    <div class="form-section mt-4">
                        <h3 class="form-section-title">Additional Rates</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bit_change_rate" class="form-label">
                                    Bit Change Rate
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="bit_change_rate" name="bit_change_rate" 
                                           class="form-control" value="<?php echo $rates['bit_change_rate']; ?>">
                                </div>
                                <small class="form-text">Cost per bit change</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="customize_rate" class="form-label">
                                    Customization Rate
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="customize_rate" name="customize_rate" 
                                           class="form-control" value="<?php echo $rates['customize_rate']; ?>">
                                </div>
                                <small class="form-text">Base customization charge</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="overhead_rate" class="form-label">
                                    Overhead Rate
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="overhead_rate" name="overhead_rate" 
                                           class="form-control" value="<?php echo $rates['overhead_rate']; ?>">
                                </div>
                                <small class="form-text">Overhead cost rate</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="sqf_milling_rate" class="form-label">
                                    Square Foot Milling Rate
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="sqf_milling_rate" name="sqf_milling_rate" 
                                           class="form-control" value="<?php echo $rates['sqf_milling_rate']; ?>">
                                </div>
                                <small class="form-text">Cost per square foot of milling</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="packaging_rate" class="form-label">
                                    Packaging Rate
                                </label>
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: bold;">$</span>
                                    <input type="number" step="0.01" id="packaging_rate" name="packaging_rate" 
                                           class="form-control" value="<?php echo $rates['packaging_rate']; ?>">
                                </div>
                                <small class="form-text">Base packaging cost</small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions mt-4">
                        <button type="submit" name="update_rates" class="btn btn-primary">
                            <span class="icon">💾</span> Save Rates
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                            <span class="icon">✖️</span> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Last Updated Info -->
        <?php if (isset($rates['updated_at'])): ?>
        <div class="card mt-4">
            <div class="card-body">
                <p style="margin: 0; color: #666;">
                    <strong>Last Updated:</strong> <?php echo date('F d, Y g:i A', strtotime($rates['updated_at'])); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

    </main>
</body>
</html>