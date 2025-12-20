<?php
require_once realpath(dirname(__FILE__) . '/../config.php');
require_once BASE_PATH . '/Models/Settings.php';
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EtsyModel.php';

use Models\Settings;
use MyApp\Models\Database;
use MyApp\Models\EtsyModel;

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$settingsModel = new Settings();
$settings = $settingsModel->getSettings();

// Initialize variables with existing values
$company_name = $settings['company_name'] ?? '';
$company_slogan = $settings['company_slogan'] ?? '';
$company_address = $settings['company_address'] ?? '';
$company_city = $settings['company_city'] ?? '';
$company_state = $settings['company_state'] ?? '';
$company_zip = $settings['company_zip'] ?? '';
$company_phone = $settings['company_phone'] ?? '';
$company_email = $settings['company_email'] ?? '';
$company_logo = $settings['company_logo'] ?? '';
$smtp_host = $settings['smtp_host'] ?? '';
$smtp_port = $settings['smtp_port'] ?? 587;
$smtp_username = $settings['smtp_username'] ?? '';
$smtp_password = $settings['smtp_password'] ?? '';
$smtp_from_email = $settings['smtp_from_email'] ?? '';
$smtp_from_name = $settings['smtp_from_name'] ?? '';
$smtp_encryption = $settings['smtp_encryption'] ?? 'tls';

// Get pricing rates from setup table
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getPdo();

// Initialize Etsy Model to check connection status
$etsyModel = new EtsyModel($conn);
$etsyConnected = $etsyModel->isConnected();
$etsyShopId = $etsyModel->getShopId();

// Get Etsy settings
$etsyQuery = "SELECT etsy_shop_name, etsy_last_sync, etsy_connected FROM settings WHERE id = 1";
$etsyStmt = $conn->prepare($etsyQuery);
$etsyStmt->execute();
$etsySettings = $etsyStmt->fetch(PDO::FETCH_ASSOC);
$etsyShopName = $etsySettings['etsy_shop_name'] ?? null;
$etsyLastSync = $etsySettings['etsy_last_sync'] ?? null;

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
    // Handle logo upload
    $logo_path = $company_logo; // Keep existing logo by default
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = BASE_PATH . 'public/images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
        $new_filename = 'company_logo_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
            $logo_path = 'public/images/' . $new_filename;
            
            // Delete old logo if it exists
            if (!empty($company_logo) && file_exists(BASE_PATH . $company_logo)) {
                unlink(BASE_PATH . $company_logo);
            }
        }
    }
    
    // Update company settings
    $company_name = $_POST['company_name'] ?? '';
    $company_slogan = $_POST['company_slogan'] ?? '';
    $company_address = $_POST['company_address'] ?? '';
    $company_city = $_POST['company_city'] ?? '';
    $company_state = $_POST['company_state'] ?? '';
    $company_zip = $_POST['company_zip'] ?? '';
    $company_phone = $_POST['company_phone'] ?? '';
    $company_email = $_POST['company_email'] ?? '';
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = intval($_POST['smtp_port'] ?? 587);
    $smtp_username = $_POST['smtp_username'] ?? '';
    $smtp_password = $_POST['smtp_password'] ?? '';
    $smtp_from_email = $_POST['smtp_from_email'] ?? '';
    $smtp_from_name = $_POST['smtp_from_name'] ?? '';
    $smtp_encryption = $_POST['smtp_encryption'] ?? 'tls';
    
    $settingsModel->saveSettings($company_name, $company_slogan, $company_address, 
                                 $company_city, $company_state, $company_zip, 
                                 $company_phone, $company_email, $logo_path,
                                 $smtp_host, $smtp_port, $smtp_username, $smtp_password,
                                 $smtp_from_email, $smtp_from_name, $smtp_encryption);
    
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
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="notification notification-success mb-4">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="notification notification-error mb-4">
                ❌ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form action="settings.php" method="post" enctype="multipart/form-data">
            <!-- Company Settings -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Company Information</h2>
                    <p class="card-subtitle">Basic company details and branding</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_name" class="form-label">Company Name *</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="company_slogan" class="form-label">Company Slogan</label>
                            <input type="text" id="company_slogan" name="company_slogan" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_slogan); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_logo" class="form-label">Company Logo</label>
                            <?php if (!empty($company_logo) && file_exists(BASE_PATH . $company_logo)): ?>
                                <div style="margin-bottom: 1rem;">
                                    <img src="<?php echo BASE_URL . $company_logo; ?>" alt="Current Logo" 
                                         style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; padding: 0.5rem; border-radius: 4px;">
                                    <p style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">Current logo</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="company_logo" name="company_logo" class="form-control" 
                                   accept="image/*">
                            <small class="form-text">Upload a new logo (PNG, JPG, or GIF recommended)</small>
                        </div>
                    </div>
                    
                    <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Contact Information</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_phone" class="form-label">Phone Number</label>
                            <input type="tel" id="company_phone" name="company_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_phone); ?>" 
                                   placeholder="(555) 123-4567">
                        </div>
                        <div class="form-group">
                            <label for="company_email" class="form-label">Email Address</label>
                            <input type="email" id="company_email" name="company_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_email); ?>" 
                                   placeholder="info@company.com">
                        </div>
                    </div>
                    
                    <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 600;">Business Address</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_address" class="form-label">Street Address</label>
                            <input type="text" id="company_address" name="company_address" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_address); ?>" 
                                   placeholder="123 Main Street">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_city" class="form-label">City</label>
                            <input type="text" id="company_city" name="company_city" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_city); ?>" 
                                   placeholder="City">
                        </div>
                        <div class="form-group">
                            <label for="company_state" class="form-label">State</label>
                            <input type="text" id="company_state" name="company_state" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_state); ?>" 
                                   placeholder="AR" maxlength="2" style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label for="company_zip" class="form-label">Zip Code</label>
                            <input type="text" id="company_zip" name="company_zip" class="form-control" 
                                   value="<?php echo htmlspecialchars($company_zip); ?>" 
                                   placeholder="12345" maxlength="10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Email Configuration</h2>
                    <p class="card-subtitle">SMTP settings for sending estimate emails to customers</p>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_host" class="form-label">SMTP Server</label>
                            <input type="text" id="smtp_host" name="smtp_host" class="form-control" 
                                   value="<?php echo htmlspecialchars($smtp_host); ?>" 
                                   placeholder="smtp.gmail.com">
                            <small class="form-text">Your email provider's SMTP server address</small>
                        </div>
                        <div class="form-group">
                            <label for="smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" class="form-control" 
                                   value="<?php echo $smtp_port; ?>" 
                                   placeholder="587">
                            <small class="form-text">Usually 587 for TLS or 465 for SSL</small>
                        </div>
                        <div class="form-group">
                            <label for="smtp_encryption" class="form-label">Encryption</label>
                            <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                                <option value="tls" <?php echo $smtp_encryption === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $smtp_encryption === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo $smtp_encryption === 'none' ? 'selected' : ''; ?>>None</option>
                            </select>
                            <small class="form-text">Security protocol (TLS recommended)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username" class="form-control" 
                                   value="<?php echo htmlspecialchars($smtp_username); ?>" 
                                   placeholder="your-email@example.com">
                            <small class="form-text">Your email login username</small>
                        </div>
                        <div class="form-group">
                            <label for="smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" class="form-control" 
                                   value="<?php echo htmlspecialchars($smtp_password); ?>" 
                                   placeholder="••••••••">
                            <small class="form-text">Your email password or app-specific password</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_from_name" class="form-label">From Name</label>
                            <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($smtp_from_name); ?>" 
                                   placeholder="<?php echo htmlspecialchars($company_name); ?>">
                            <small class="form-text">Name shown to recipients</small>
                        </div>
                        <div class="form-group">
                            <label for="smtp_from_email" class="form-label">From Email</label>
                            <input type="email" id="smtp_from_email" name="smtp_from_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($smtp_from_email); ?>" 
                                   placeholder="estimates@yourcompany.com">
                            <small class="form-text">Email address shown to recipients</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>📧 Gmail Users:</strong> Use smtp.gmail.com (port 587, TLS). You'll need to enable 
                        "Less secure app access" or create an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a>.
                    </div>
                </div>
            </div>

            <!-- Etsy Integration -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">🛒 Etsy Integration</h2>
                    <p class="card-subtitle">Connect your Etsy shop to sync orders and create estimates</p>
                </div>
                <div class="card-body">
                    <?php if ($etsyConnected): ?>
                        <!-- Connected State -->
                        <div class="alert alert-success mb-4">
                            <strong>✅ Connected to Etsy</strong><br>
                            Shop: <strong><?php echo htmlspecialchars($etsyShopName ?? 'Unknown'); ?></strong>
                            <?php if ($etsyShopId): ?>
                                (Shop ID: <?php echo htmlspecialchars($etsyShopId); ?>)
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($etsyLastSync): ?>
                            <p style="margin-bottom: 1rem;">
                                <strong>Last Sync:</strong> 
                                <?php echo date('F j, Y g:i A', strtotime($etsyLastSync)); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="form-actions">
                            <a href="<?php echo BASE_URL; ?>public/etsy/sync_orders.php" class="btn btn-primary">
                                <span class="icon">🔄</span> Sync Orders Now
                            </a>
                            <a href="<?php echo BASE_URL; ?>public/etsy/disconnect.php" class="btn btn-secondary" 
                               onclick="return confirm('Are you sure you want to disconnect from Etsy? You will need to re-authorize to sync orders again.');">
                                <span class="icon">🔌</span> Disconnect
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Disconnected State -->
                        <div class="alert alert-info mb-4">
                            <strong>🔌 Not Connected</strong><br>
                            Connect your Etsy shop to automatically import orders and create estimates.
                        </div>
                        
                        <h4 style="margin-bottom: 0.75rem; font-size: 1rem; font-weight: 600;">What happens when you connect?</h4>
                        <ul style="margin-bottom: 1.5rem; padding-left: 1.5rem; color: #666;">
                            <li>Sync your Etsy orders automatically</li>
                            <li>Import customer information and shipping addresses</li>
                            <li>Create estimates directly from Etsy orders</li>
                            <li>Track fulfillment and order status</li>
                        </ul>
                        
                        <?php
                        // Generate OAuth URL
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'];
                        $redirectUri = $protocol . '://' . $host . '/omc/public/etsy/oauth_callback.php';
                        $state = bin2hex(random_bytes(16)); // CSRF protection
                        
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        $_SESSION['etsy_oauth_state'] = $state;
                        
                        try {
                            $authUrl = $etsyModel->getAuthorizationUrl($redirectUri, $state);
                        } catch (Exception $e) {
                            $authUrl = '#';
                            echo '<div class="alert alert-danger mb-3">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                        
                        <div class="form-actions">
                            <a href="<?php echo htmlspecialchars($authUrl); ?>" class="btn btn-primary">
                                <span class="icon">🔗</span> Connect to Etsy
                            </a>
                        </div>
                        
                        <div class="alert alert-warning mt-3" style="font-size: 0.875rem;">
                            <strong>Note:</strong> You'll be redirected to Etsy to authorize this application. 
                            Make sure you're logged into your Etsy seller account.
                        </div>
                    <?php endif; ?>
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
