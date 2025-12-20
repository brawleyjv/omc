<?php
/**
 * Etsy Order Sync Handler
 * 
 * This file will handle syncing orders from Etsy API
 * (Full implementation coming in Phase 2 - Order Sync)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/EtsyModel.php';

use MyApp\Models\Database;
use MyApp\Models\EtsyModel;

session_start();

// Check authentication
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

try {
    // Initialize database
    $database = new Database();
    $db = $database->connect();
    $etsyModel = new EtsyModel($db);
    
    // Check if connected
    if (!$etsyModel->isConnected()) {
        throw new Exception('Not connected to Etsy. Please connect in Settings first.');
    }
    
    // TODO: Phase 2 - Implement actual order sync
    // This is a placeholder that will be implemented in Phase 2
    
    // For now, just show a message that sync is not yet implemented
    $_SESSION['error_message'] = 'Order sync functionality will be available once your Etsy app is approved. The OAuth authentication flow must be tested first.';
    
    // Uncomment this when implementing Phase 2:
    /*
    $startTime = microtime(true);
    $stats = [
        'started_at' => date('Y-m-d H:i:s'),
        'processed' => 0,
        'added' => 0,
        'updated' => 0,
        'failed' => 0,
        'api_calls' => 0
    ];
    
    try {
        // Get shop ID
        $shopId = $etsyModel->getShopId();
        
        // Fetch receipts (orders) from Etsy API
        $receipts = $etsyModel->makeApiRequest('GET', "/shops/{$shopId}/receipts", [
            'was_paid' => true,
            'limit' => 100
        ]);
        
        $stats['api_calls']++;
        
        if (isset($receipts['results'])) {
            foreach ($receipts['results'] as $receipt) {
                $stats['processed']++;
                
                // Check if order already exists
                $checkQuery = "SELECT id FROM etsy_orders WHERE etsy_order_id = :order_id";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->execute([':order_id' => $receipt['receipt_id']]);
                $existing = $checkStmt->fetch();
                
                // Prepare order data
                $orderData = [
                    'etsy_order_id' => $receipt['receipt_id'],
                    'customer_name' => $receipt['name'],
                    'customer_email' => $receipt['buyer_email'],
                    'ship_name' => $receipt['name'],
                    'ship_address1' => $receipt['first_line'],
                    'ship_address2' => $receipt['second_line'],
                    'ship_city' => $receipt['city'],
                    'ship_state' => $receipt['state'],
                    'ship_zip' => $receipt['zip'],
                    'ship_country' => $receipt['country_iso'],
                    'items_count' => $receipt['quantity'],
                    'order_total' => $receipt['grandtotal'],
                    'fulfillment_status' => $receipt['was_shipped'] ? 'shipped' : 'pending',
                    'items_data' => json_encode($receipt['transactions']),
                    'order_data' => json_encode($receipt)
                ];
                
                if ($existing) {
                    // Update existing order
                    $updateQuery = "UPDATE etsy_orders SET 
                        customer_name = :customer_name,
                        customer_email = :customer_email,
                        ship_name = :ship_name,
                        ship_address1 = :ship_address1,
                        ship_address2 = :ship_address2,
                        ship_city = :ship_city,
                        ship_state = :ship_state,
                        ship_zip = :ship_zip,
                        ship_country = :ship_country,
                        items_count = :items_count,
                        order_total = :order_total,
                        fulfillment_status = :fulfillment_status,
                        items_data = :items_data,
                        order_data = :order_data,
                        updated_at = NOW()
                        WHERE etsy_order_id = :etsy_order_id";
                    
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->execute($orderData);
                    $stats['updated']++;
                } else {
                    // Insert new order
                    $insertQuery = "INSERT INTO etsy_orders (
                        etsy_order_id, customer_name, customer_email, ship_name,
                        ship_address1, ship_address2, ship_city, ship_state,
                        ship_zip, ship_country, items_count, order_total,
                        fulfillment_status, items_data, order_data
                    ) VALUES (
                        :etsy_order_id, :customer_name, :customer_email, :ship_name,
                        :ship_address1, :ship_address2, :ship_city, :ship_state,
                        :ship_zip, :ship_country, :items_count, :order_total,
                        :fulfillment_status, :items_data, :order_data
                    )";
                    
                    $insertStmt = $db->prepare($insertQuery);
                    $insertStmt->execute($orderData);
                    $stats['added']++;
                }
            }
        }
        
        // Update last sync timestamp
        $updateQuery = "UPDATE settings SET etsy_last_sync = NOW() WHERE id = 1";
        $stmt = $db->prepare($updateQuery);
        $stmt->execute();
        
        // Log successful sync
        $etsyModel->logSync('orders', 'success', $stats);
        
        $_SESSION['success_message'] = "Successfully synced {$stats['processed']} orders. " .
                                      "Added: {$stats['added']}, Updated: {$stats['updated']}";
        
    } catch (Exception $e) {
        $stats['failed'] = $stats['processed'];
        $etsyModel->logSync('orders', 'failure', $stats, $e->getMessage());
        throw $e;
    }
    */
    
} catch (Exception $e) {
    error_log('Etsy sync error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Sync failed: ' . htmlspecialchars($e->getMessage());
}

// Redirect back
$referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'Views/settings.php';
header('Location: ' . $referer);
exit;
?>
