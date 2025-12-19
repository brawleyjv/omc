<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/EstimateModel.php';

use MyApp\Models\Database;

// Get estimate by ID
$estimateId = $_GET['id'] ?? null;
$estimate = null;
$sent = false;
$error = null;

if ($estimateId) {
    try {
        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn = $database->getPdo();
        $estimateModel = new EstimateModel($conn);
        $estimate = $estimateModel->getEstimateById($estimateId);
    } catch (Exception $e) {
        error_log("Error loading estimate: " . $e->getMessage());
        $error = "Error loading estimate";
    }
}

if (!$estimate) {
    header("Location: " . BASE_URL . "Views/estimate/list_estimates.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toEmail = $_POST['to_email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($toEmail)) {
        $error = "Email address is required";
    } else {
        try {
            // Build email body
            $emailBody = $message . "\n\n";
            $emailBody .= "===========================================\n";
            $emailBody .= "ESTIMATE SUMMARY\n";
            $emailBody .= "===========================================\n\n";
            $emailBody .= "Estimate Number: " . $estimate['estimate_number'] . "\n";
            $emailBody .= "Project: " . $estimate['project_name'] . "\n";
            $emailBody .= "Customer: " . $estimate['customer_name'] . "\n";
            $emailBody .= "Date: " . date('F d, Y', strtotime($estimate['created_at'])) . "\n\n";
            
            if ($estimate['project_description']) {
                $emailBody .= "Description:\n" . $estimate['project_description'] . "\n\n";
            }
            
            $emailBody .= "TOTAL ESTIMATE: $" . number_format($estimate['total_estimate'], 2) . "\n\n";
            $emailBody .= "===========================================\n\n";
            $emailBody .= "To view the full detailed estimate, please visit:\n";
            $emailBody .= BASE_URL . "Views/estimate/print_estimate.php?id=" . $estimate['id'] . "\n\n";
            $emailBody .= "Thank you for your business!\n";
            $emailBody .= "Ozark Made Crafts\n";
            
            // Email headers
            $headers = "From: Ozark Made Crafts <noreply@ozarkmadecrafts.com>\r\n";
            $headers .= "Reply-To: " . ($estimate['customer_email'] ?? 'noreply@ozarkmadecrafts.com') . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Send email
            if (mail($toEmail, $subject, $emailBody, $headers)) {
                // Update estimate status to sent
                $estimateModel->updateStatus($estimateId, 'sent');
                $sent = true;
            } else {
                $error = "Failed to send email. Please check your mail server configuration.";
            }
            
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            $error = "Error sending email: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Estimate - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Email Estimate</h1>
                    <p><?php echo htmlspecialchars($estimate['estimate_number']); ?></p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="nav-link">All Estimates</a>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <?php if ($sent): ?>
            <div class="notification notification-success">
                <h3>✅ Email Sent Successfully!</h3>
                <p>The estimate has been sent to <strong><?php echo htmlspecialchars($_POST['to_email']); ?></strong></p>
                <div class="form-actions mt-4">
                    <a href="<?php echo BASE_URL; ?>Views/estimate/view_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-primary">
                        View Estimate
                    </a>
                    <a href="<?php echo BASE_URL; ?>Views/estimate/list_estimates.php" class="btn btn-secondary">
                        All Estimates
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Send Estimate via Email</h2>
                    <p class="card-subtitle">Email estimate to customer: <?php echo htmlspecialchars($estimate['customer_name']); ?></p>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="notification notification-error">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-group">
                            <label for="to_email" class="form-label">To Email Address *</label>
                            <input type="email" id="to_email" name="to_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($estimate['customer_email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   value="Your Project Estimate - <?php echo htmlspecialchars($estimate['project_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="message" class="form-label">Message</label>
                            <textarea id="message" name="message" class="form-control" rows="6">Dear <?php echo htmlspecialchars($estimate['customer_name']); ?>,

Thank you for your interest in working with Ozark Made Crafts! Please find below your project estimate for "<?php echo htmlspecialchars($estimate['project_name']); ?>".

We look forward to bringing your project to life with our precision craftsmanship.

If you have any questions or would like to proceed, please don't hesitate to contact us.

Best regards,
Ozark Made Crafts Team</textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon">📧</span> Send Email
                            </button>
                            <a href="<?php echo BASE_URL; ?>Views/estimate/view_estimate.php?id=<?php echo $estimate['id']; ?>" class="btn btn-secondary">
                                <span class="icon">✖️</span> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estimate Preview -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">Estimate Preview</h2>
                </div>
                <div class="card-body">
                    <p><strong>Estimate #:</strong> <?php echo htmlspecialchars($estimate['estimate_number']); ?></p>
                    <p><strong>Project:</strong> <?php echo htmlspecialchars($estimate['project_name']); ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($estimate['total_estimate'], 2); ?></p>
                    <a href="<?php echo BASE_URL; ?>Views/estimate/print_estimate.php?id=<?php echo $estimate['id']; ?>" target="_blank" class="btn btn-ghost btn-sm">
                        Preview Full Estimate
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>