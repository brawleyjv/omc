<?php
require_once realpath(dirname(__FILE__) . '/../config.php');
require_once BASE_PATH . '/Models/Settings.php';
require_once BASE_PATH . 'Models/Database.php';

use Models\Settings;
use MyApp\Models\Database;

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$settingsModel = new Settings();
$settings = $settingsModel->getSettings();

// Initialize variables with existing values
$company_name = $settings['company_name'] ?? '';
$company_slogan = $settings['company_slogan'] ?? '';

// Get pricing rates from setup table
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getPdo();
$ratesQuery = "SELECT mill_rate, laser_rate, labor_rate, bit_change_rate, customize_rate FROM setup LIMIT 1";
$ratesStmt = $conn->prepare($ratesQuery);
$ratesStmt->execute();
$rates = $ratesStmt->fetch(PDO::FETCH_ASSOC);

$mill_rate = $rates['mill_rate'] ?? 0.85;
$laser_rate = $rates['laser_rate'] ?? 0.50;
$labor_rate = $rates['labor_rate'] ?? 25.00;
$bit_change_rate = $rates['bit_change_rate'] ?? 5.00;
$customize_rate = $rates['customize_rate'] ?? 5.00;

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update company settings
    $company_name = $_POST['company_name'] ?? '';
    $company_slogan = $_POST['company_slogan'] ?? '';
    $settingsModel->saveSettings($company_name, $company_slogan);
    
    // Update pricing rates
    $updateQuery = "UPDATE setup SET 
        mill_rate = :mill_rate,
        laser_rate = :laser_rate,
        labor_rate = :labor_rate,
        bit_change_rate = :bit_change_rate,
        customize_rate = :customize_rate
        WHERE id = 1";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([
        ':mill_rate' => $_POST['mill_rate'],
        ':laser_rate' => $_POST['laser_rate'],
        ':labor_rate' => $_POST['labor_rate'],
        ':bit_change_rate' => $_POST['bit_change_rate'],
        ':customize_rate' => $_POST['customize_rate']
    ]);
    
    $message = 'Settings updated successfully!';
    
    // Refresh values
    header("Location: " . BASE_URL . "Views/settings.php?updated=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - OMC</title>
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
                    <h1>Settings</h1>
                    <p>Configure company information and pricing rates</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <?php if (isset($_GET['updated'])): ?>
            <div class="notification notification-success mb-4">
                ✅ Settings updated successfully!
            </div>
        <?php endif; ?>

        <form action="settings.php" method="post">
            <!-- Company Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Company Information</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_name); ?>">
                        </div>
                        <div class="form-group">
                            <label for="company_slogan" class="form-label">Company Slogan</label>
                            <input type="text" id="company_slogan" name="company_slogan" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_slogan); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Rates -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Pricing Rates</h2>
                    <p class="card-subtitle">Set your hourly and per-minute rates for estimates</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mill_rate" class="form-label">CNC/Router Rate ($ per minute)</label>
                            <input type="number" step="0.01" id="mill_rate" name="mill_rate" class="form-control" 
                                   value="<?php echo $mill_rate; ?>" required>
                            <small class="form-text">Cost per minute of CNC router/mill time</small>
                        </div>
                        <div class="form-group">
                            <label for="laser_rate" class="form-label">Laser Rate ($ per minute)</label>
                            <input type="number" step="0.01" id="laser_rate" name="laser_rate" class="form-control" 
                                   value="<?php echo $laser_rate; ?>" required>
                            <small class="form-text">Cost per minute of laser time</small>
                        </div>
                        <div class="form-group">
                            <label for="labor_rate" class="form-label">Labor Rate ($ per hour)</label>
                            <input type="number" step="0.01" id="labor_rate" name="labor_rate" class="form-control" 
                                   value="<?php echo $labor_rate; ?>" required>
                            <small class="form-text">Cost per hour of manual labor</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bit_change_rate" class="form-label">Bit Change Rate ($)</label>
                            <input type="number" step="0.01" id="bit_change_rate" name="bit_change_rate" class="form-control" 
                                   value="<?php echo $bit_change_rate; ?>" required>
                            <small class="form-text">Cost per CNC bit change</small>
                        </div>
                        <div class="form-group">
                            <label for="customize_rate" class="form-label">Customization Rate ($)</label>
                            <input type="number" step="0.01" id="customize_rate" name="customize_rate" class="form-control" 
                                   value="<?php echo $customize_rate; ?>" required>
                            <small class="form-text">Fixed cost when customization is required</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Note:</strong> Shipping/packaging costs are entered per-estimate as they vary by order.
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <span class="icon">💾</span> Save Settings
                </button>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">✖️</span> Cancel
                </a>
            </div>
        </form>
    </main>
</body>
</html>
