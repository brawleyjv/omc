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
$settings = null;

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $millRate = floatval($_POST['mill_rate']);
        $laserRate = floatval($_POST['laser_rate']);
        $laborRate = floatval($_POST['labor_rate']);
        $bitChangeRate = floatval($_POST['bit_change_rate']);
        $customizeRate = floatval($_POST['customize_rate']);
        $overheadRate = floatval($_POST['overhead_rate']);
        $packagingRate = floatval($_POST['packaging_rate']);
        $sqfMillingRate = floatval($_POST['sqf_milling_rate']);
        
        $updateQuery = "UPDATE setup SET 
            mill_rate = :mill_rate,
            laser_rate = :laser_rate,
            labor_rate = :labor_rate,
            bit_change_rate = :bit_change_rate,
            customize_rate = :customize_rate,
            overhead_rate = :overhead_rate,
            packaging_rate = :packaging_rate,
            sqf_milling_rate = :sqf_milling_rate
            WHERE id = 1";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([
            ':mill_rate' => $millRate,
            ':laser_rate' => $laserRate,
            ':labor_rate' => $laborRate,
            ':bit_change_rate' => $bitChangeRate,
            ':customize_rate' => $customizeRate,
            ':overhead_rate' => $overheadRate,
            ':packaging_rate' => $packagingRate,
            ':sqf_milling_rate' => $sqfMillingRate
        ]);
        
        $success = true;
    }
    
    // Load current settings
    $query = "SELECT * FROM setup LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error in settings: " . $e->getMessage());
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Settings - OMC</title>
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
                    <h1>Pricing & Rate Settings</h1>
                    <p>Manage hourly rates and pricing calculations</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="nav-link">Estimates</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Pricing Settings</h1>
                <p class="page-subtitle">Update rates used in estimate calculations</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="notification notification-success mb-4">
                ✅ Settings updated successfully!
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notification notification-error mb-4">
                ❌ Error: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($settings): ?>
        <form method="post" action="">
            <!-- Primary Rates -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⚙️ Machine & Labor Rates</h2>
                    <p class="card-subtitle">Primary rates used in estimate calculations</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mill_rate" class="form-label">
                                CNC Router/Mill Rate
                                <small style="color: #666; display: block;">$ per minute</small>
                            </label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="mill_rate" name="mill_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['mill_rate']; ?>" required>
                            </div>
                            <small class="form-text">Rate charged per minute of router/CNC time</small>
                        </div>

                        <div class="form-group">
                            <label for="laser_rate" class="form-label">
                                Laser Rate
                                <small style="color: #666; display: block;">$ per minute</small>
                            </label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="laser_rate" name="laser_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['laser_rate']; ?>" required>
                            </div>
                            <small class="form-text">Rate charged per minute of laser time</small>
                        </div>

                        <div class="form-group">
                            <label for="labor_rate" class="form-label">
                                Labor Rate
                                <small style="color: #666; display: block;">$ per hour</small>
                            </label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="labor_rate" name="labor_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['labor_rate']; ?>" required>
                            </div>
                            <small class="form-text">Hourly rate for manual labor</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Rates -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">💰 Additional Rates</h2>
                    <p class="card-subtitle">Other pricing factors</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bit_change_rate" class="form-label">Bit Change Rate</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="bit_change_rate" name="bit_change_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['bit_change_rate']; ?>" required>
                            </div>
                            <small class="form-text">Charge per tool/bit change</small>
                        </div>

                        <div class="form-group">
                            <label for="customize_rate" class="form-label">Customization Rate</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="customize_rate" name="customize_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['customize_rate']; ?>" required>
                            </div>
                            <small class="form-text">Rate for custom work</small>
                        </div>

                        <div class="form-group">
                            <label for="overhead_rate" class="form-label">Overhead Rate</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="overhead_rate" name="overhead_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['overhead_rate']; ?>" required>
                            </div>
                            <small class="form-text">General overhead charge</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="packaging_rate" class="form-label">Packaging Rate</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="packaging_rate" name="packaging_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['packaging_rate']; ?>" required>
                            </div>
                            <small class="form-text">Charge for packaging materials</small>
                        </div>

                        <div class="form-group">
                            <label for="sqf_milling_rate" class="form-label">Square Foot Milling Rate</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 10px; color: #666;">$</span>
                                <input type="number" step="0.01" id="sqf_milling_rate" name="sqf_milling_rate" class="form-control" 
                                       style="padding-left: 24px;" value="<?php echo $settings['sqf_milling_rate']; ?>" required>
                            </div>
                            <small class="form-text">Rate per square foot for milling</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card mt-4" style="background: #fff9e6; border-left: 4px solid #f39c12;">
                <div class="card-body">
                    <h3 style="margin-top: 0; color: #f39c12;">ℹ️ How Rates Are Used</h3>
                    <p><strong>In Estimates:</strong></p>
                    <ul>
                        <li>Router Cost = Router Time (minutes) × Mill Rate</li>
                        <li>Laser Cost = Laser Time (minutes) × Laser Rate</li>
                        <li>Labor Cost = Labor Hours × Labor Rate</li>
                        <li>Final estimate uses formula: <code>(materials_cost / 0.3) + ((labor_hours × labor_rate) / 0.2) + machine_cost</code></li>
                        <li>Machine time (router/laser) is charged at cost with no markup</li>
                    </ul>
                    <p style="margin-bottom: 0;"><strong>Note:</strong> Changes to these rates will apply to all new estimates. Existing estimates keep their original calculated values.</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <span class="icon">💾</span> Save Settings
                </button>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">✖️</span> Cancel
                </a>
            </div>
        </form>
        <?php else: ?>
            <div class="notification notification-error">
                No settings found in database. Please contact administrator.
            </div>
        <?php endif; ?>
    </main>
</body>
</html>