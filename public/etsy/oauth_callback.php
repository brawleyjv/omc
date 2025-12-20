<?php
/**
 * Etsy OAuth Callback Handler
 * 
 * This file handles the OAuth callback from Etsy after user authorization.
 * It exchanges the authorization code for access tokens and stores them.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/EtsyModel.php';

use MyApp\Models\Database;
use MyApp\Models\EtsyModel;

session_start();

// Get authorization code and state from callback
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$error = $_GET['error'] ?? null;

// Check for errors
if ($error) {
    $_SESSION['error_message'] = 'Etsy authorization failed: ' . htmlspecialchars($error);
    header('Location: /omc/Views/settings.php');
    exit;
}

// Validate state to prevent CSRF attacks
$expectedState = $_SESSION['etsy_oauth_state'] ?? null;
if (!$state || $state !== $expectedState) {
    $_SESSION['error_message'] = 'Invalid OAuth state. Please try again.';
    header('Location: /omc/Views/settings.php');
    exit;
}

// Validate authorization code
if (!$code) {
    $_SESSION['error_message'] = 'No authorization code received from Etsy.';
    header('Location: /omc/Views/settings.php');
    exit;
}

try {
    // Initialize database and Etsy model
    $database = new Database();
    $db = $database->connect();
    $etsyModel = new EtsyModel($db);
    
    // Build redirect URI (must match exactly what was sent in authorization request)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $redirectUri = $protocol . '://' . $host . '/omc/public/etsy/oauth_callback.php';
    
    // Exchange authorization code for access token
    $tokenResponse = $etsyModel->exchangeCodeForToken($code, $redirectUri);
    
    // Update last sync timestamp
    $query = "UPDATE settings SET etsy_last_sync = NOW() WHERE id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    // Success! Redirect to settings page with success message
    $_SESSION['success_message'] = 'Successfully connected to Etsy! Shop: ' . 
                                   ($tokenResponse['shop_name'] ?? 'Connected');
    header('Location: /omc/Views/settings.php');
    exit;
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log('Etsy OAuth callback error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to connect to Etsy: ' . htmlspecialchars($e->getMessage());
    header('Location: /omc/Views/settings.php');
    exit;
}
?>
