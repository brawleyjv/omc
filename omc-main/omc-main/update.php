<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Controllers/UpdateController.php';

// Check for action in both POST and GET requests
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action) {
    error_log("update.php: Received action - " . $action); // Log received action
    $updateController = new UpdateController();

    switch ($action) {
        case 'manualBackup': // This will now represent the "Update Database" button
            error_log("update.php: Update Database action triggered."); // Log action trigger
            $updateController->manualBackup();
            break;
        default:
            error_log("update.php: Unknown action - " . $action); // Log unknown action
            echo "<p style='color: red; text-align: center;'>Unknown action: " . htmlspecialchars($action) . "</p>";
            break;
    }
}
?>