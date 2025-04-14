<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__) . '/../config.php'); // Load config
require_once BASE_PATH . '/Controllers/UpdateController.php'; // Ensure UpdateController.php is included

use MyApp\Controllers\UpdateController; // Correct namespace for UpdateController

// Initialize the UpdateController
$updateController = new UpdateController();

// Get the action from the query string
$action = $_GET['action'] ?? '';

if (empty($action)) {
    echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: No action specified.</p>";
    exit;
}

// Handle the specified action
try {
    switch ($action) {
        case 'backup_database':
            $updateController->backupDatabase();
            break;

        case 'download_zip':
            $updateController->downloadZip();
            break;

        case 'import_database':
            $updateController->importDatabase();
            break;

        case 'update_database':
            $updateController->updateDatabase();
            break;

        case 'manual_backup':
            $updateController->manualBackup();
            break;

        default:
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: Unknown action '$action'.</p>";
            break;
    }
} catch (Exception $e) {
    error_log("Error handling action '$action': " . $e->getMessage());
    echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>