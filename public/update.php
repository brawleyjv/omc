<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/header.php'; // Include the header
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Controllers/UpdateController.php'; // Include the UpdateController

$controller = new UpdateController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    error_log("update.php: Received action: $action"); // Log the action for debugging
    echo "<p style='color: blue; text-align: center;'>Action received: $action</p>"; // Display action for debugging
    switch ($action) {
        case 'download_zip':
            error_log("update.php: Calling downloadZip() method.");
            $controller->downloadZip();
            break;
        case 'update_database':
            error_log("update.php: Calling updateDatabase() method.");
            // Remove or comment out the following line if the method is not needed
            // $controller->updateDatabase();
            echo "<p style='color: red; text-align: center;'>The updateDatabase action is not implemented.</p>";
            break;
        case 'manual_backup':
            error_log("update.php: Calling manualBackup() method.");
            // Remove or comment out the following line if the method is not needed
            // $controller->manualBackup();
            echo "<p style='color: red; text-align: center;'>The manualBackup action is not implemented.</p>";
            break;
        case 'backup_database':
            error_log("update.php: Calling backupDatabase() method.");
            $controller->backupDatabase();
            break;
        case 'import_database':
            error_log("update.php: Calling importDatabase() method.");
            $controller->importDatabase(); // No arguments needed
            break;
        default:
            error_log("update.php: Invalid action specified.");
            echo "<p style='color: red; text-align: center;'>Invalid action specified.</p>";
    }
} else {
    error_log("update.php: No action specified.");
    require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/update.php'; // Include the HTML view
}
?>