<?php
/**
 * Etsy Disconnect Handler
 * 
 * This file handles disconnecting from Etsy (clearing all tokens and connection data)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/EtsyModel.php';

use MyApp\Models\Database;
use MyApp\Models\EtsyModel;

session_start();

try {
    // Initialize database and Etsy model
    $database = new Database();
    $db = $database->connect();
    $etsyModel = new EtsyModel($db);
    
    // Disconnect from Etsy
    $etsyModel->disconnect();
    
    // Success message
    $_SESSION['success_message'] = 'Successfully disconnected from Etsy.';
    
} catch (Exception $e) {
    error_log('Etsy disconnect error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to disconnect from Etsy: ' . htmlspecialchars($e->getMessage());
}

// Redirect back to settings
header('Location: /omc/Views/settings.php');
exit;
?>
