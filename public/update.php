<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/header.php'; // Include the header
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Controllers/UpdateController.php'; // Include the UpdateController

$controller = new UpdateController();

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'download_zip':
            $controller->downloadZip();
            break;
        case 'update_database':
            $controller->updateDatabase();
            break;
        case 'restore_backup':
            $controller->restoreBackup();
            break;
        case 'manual_backup':
            $controller->manualBackup();
            break;
        default:
            echo "<p style='color: red; text-align: center;'>Invalid action specified.</p>";
    }
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/update.php'; // Include the HTML view
}
?>