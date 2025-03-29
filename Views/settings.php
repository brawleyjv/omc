<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Explicitly reference the OMC directory
require_once BASE_PATH . 'Models/Settings.php'; // Use BASE_PATH for dynamic path resolution

use Models\Settings;

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
//if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    //header("Location: " . BASE_URL . "Views/login.php");
    //exit();
//}

$settingsModel = new Settings();
$settings = $settingsModel->getSettings();

// Initialize variables with existing values or empty strings
$company_name = $settings['company_name'] ?? '';
$company_slogan = $settings['company_slogan'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'] ?? '';
    $company_slogan = $_POST['company_slogan'] ?? '';

    $settingsModel->saveSettings($company_name, $company_slogan);

    // Redirect to the same page to avoid form resubmission
    header("Location: " . BASE_URL . "Views/settings.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">Settings</h1>
        <form action="settings.php" method="post">
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="company_slogan">Company Slogan:</label>
                <input type="text" id="company_slogan" name="company_slogan" value="<?php echo htmlspecialchars($company_slogan); ?>" required>
            </div>
            <button type="submit" class="btn styled-btn">Save</button>
        </form>
    </div>
</body>
</html>
