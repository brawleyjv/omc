<?php
namespace MyApp\Models;

use PDO;
use Exception;

/**
 * EtsyOrderParser - Parse and process Etsy order items
 * 
 * This model handles:
 * - Parsing items_data JSON from etsy_orders
 * - Extracting individual line items
 * - Storing in etsy_order_items table
 * - Auto-matching products to OMC projects
 * - Managing product mappings
 */
class EtsyOrderParser {
    private $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Parse items from an Etsy order and store in etsy_order_items
     * 
     * @param int $etsyOrderId The etsy_orders.id (not etsy_order_id)
     * @return array Statistics array
     */
    public function parseOrderItems($etsyOrderId) {
        $stats = [
            'items_found' => 0,
            'items_added' => 0,
            'items_matched' => 0,
            'items_unmatched' => 0
        ];
        
        try {
            // Get the order and its items_data
            $query = "SELECT id, etsy_order_id, items_data, order_data FROM etsy_orders WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $etsyOrderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                throw new Exception("Order not found: " . $etsyOrderId);
            }
            
            // Check if items_data exists and is valid JSON
            if (empty($order['items_data'])) {
                // Try to extract from order_data if items_data is empty
                $orderData = json_decode($order['order_data'], true);
                if (isset($orderData['transactions'])) {
                    $items = $orderData['transactions'];
                } else {
                    return $stats; // No items to parse
                }
            } else {
                $items = json_decode($order['items_data'], true);
            }
            
            if (!is_array($items)) {
                throw new Exception("Invalid items data format");
            }
            
            $stats['items_found'] = count($items);
            
            // Process each item
            foreach ($items as $item) {
                $itemId = $this->storeOrderItem($etsyOrderId, $item);
                
                if ($itemId) {
                    $stats['items_added']++;
                    
                    // Try to auto-match to a project
                    $matched = $this->autoMatchItemToProject($itemId, $item);
                    
                    if ($matched) {
                        $stats['items_matched']++;
                    } else {
                        $stats['items_unmatched']++;
                    }
                }
            }
            
            // Update the order's unlinked items flag
            $this->updateOrderUnlinkedFlag($etsyOrderId);
            
        } catch (Exception $e) {
            error_log('EtsyOrderParser error: ' . $e->getMessage());
            throw $e;
        }
        
        return $stats;
    }
    
    /**
     * Store a single order item in etsy_order_items table
     * 
     * @param int $etsyOrderId The etsy_orders.id
     * @param array $item Item data from Etsy
     * @return int|false The inserted item ID or false on failure
     */
    private function storeOrderItem($etsyOrderId, $item) {
        try {
            // Extract item details (adjust fields based on actual Etsy API structure)
            $listingId = $item['listing_id'] ?? null;
            $transactionId = $item['transaction_id'] ?? $item['receipt_id'] ?? null;
            $productName = $item['title'] ?? $item['product_name'] ?? 'Unknown Product';
            $productSku = $item['product_data']['sku'] ?? $item['sku'] ?? null;
            $productTitle = $item['title'] ?? null;
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['price']['amount'] ?? $item['price'] ?? 0;
            $totalPrice = $quantity * $unitPrice;
            
            // Extract variations (size, color, etc)
            $variations = null;
            if (isset($item['variations']) && is_array($item['variations'])) {
                $variations = json_encode($item['variations']);
            } elseif (isset($item['product_data']['property_values'])) {
                $variations = json_encode($item['product_data']['property_values']);
            }
            
            // Extract personalization
            $personalization = $item['personalization'] ?? $item['buyer_note'] ?? null;
            
            // Check if item already exists (avoid duplicates)
            $checkQuery = "SELECT id FROM etsy_order_items 
                          WHERE etsy_order_id = :order_id 
                          AND etsy_transaction_id = :transaction_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([
                ':order_id' => $etsyOrderId,
                ':transaction_id' => $transactionId
            ]);
            
            if ($checkStmt->fetch()) {
                return false; // Already exists
            }
            
            // Insert the item
            $insertQuery = "INSERT INTO etsy_order_items (
                etsy_order_id, etsy_listing_id, etsy_transaction_id,
                product_name, product_sku, product_title,
                quantity, unit_price, total_price,
                variations_data, personalization,
                item_data
            ) VALUES (
                :etsy_order_id, :etsy_listing_id, :etsy_transaction_id,
                :product_name, :product_sku, :product_title,
                :quantity, :unit_price, :total_price,
                :variations_data, :personalization,
                :item_data
            )";
            
            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->execute([
                ':etsy_order_id' => $etsyOrderId,
                ':etsy_listing_id' => $listingId,
                ':etsy_transaction_id' => $transactionId,
                ':product_name' => $productName,
                ':product_sku' => $productSku,
                ':product_title' => $productTitle,
                ':quantity' => $quantity,
                ':unit_price' => $unitPrice,
                ':total_price' => $totalPrice,
                ':variations_data' => $variations,
                ':personalization' => $personalization,
                ':item_data' => json_encode($item)
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log('Error storing order item: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Try to automatically match an item to an OMC project
     * 
     * @param int $itemId The etsy_order_items.id
     * @param array $item Item data
     * @return bool True if matched, false otherwise
     */
    private function autoMatchItemToProject($itemId, $item) {
        try {
            $listingId = $item['listing_id'] ?? null;
            $productName = $item['title'] ?? $item['product_name'] ?? '';
            $productSku = $item['product_data']['sku'] ?? $item['sku'] ?? null;
            
            $projectId = null;
            $matchType = null;
            $confidence = 0.0;
            
            // Try matching by listing_id first (most reliable)
            if ($listingId) {
                $query = "SELECT project_id, confidence FROM etsy_product_mappings 
                         WHERE etsy_listing_id = :listing_id AND active = TRUE 
                         LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':listing_id' => $listingId]);
                $mapping = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mapping) {
                    $projectId = $mapping['project_id'];
                    $matchType = 'listing_id';
                    $confidence = $mapping['confidence'];
                }
            }
            
            // Try matching by SKU
            if (!$projectId && $productSku) {
                $query = "SELECT project_id, confidence FROM etsy_product_mappings 
                         WHERE product_sku = :sku AND active = TRUE 
                         LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':sku' => $productSku]);
                $mapping = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mapping) {
                    $projectId = $mapping['project_id'];
                    $matchType = 'sku';
                    $confidence = $mapping['confidence'];
                }
            }
            
            // Try matching by exact product name
            if (!$projectId && $productName) {
                $query = "SELECT project_id, confidence FROM etsy_product_mappings 
                         WHERE product_name = :name AND active = TRUE 
                         LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':name' => $productName]);
                $mapping = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mapping) {
                    $projectId = $mapping['project_id'];
                    $matchType = 'name';
                    $confidence = $mapping['confidence'];
                }
            }
            
            // Try matching by pattern (LIKE)
            if (!$projectId && $productName) {
                $query = "SELECT project_id, confidence, product_title_pattern 
                         FROM etsy_product_mappings 
                         WHERE :name LIKE product_title_pattern AND active = TRUE 
                         LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':name' => $productName]);
                $mapping = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mapping) {
                    $projectId = $mapping['project_id'];
                    $matchType = 'pattern';
                    $confidence = $mapping['confidence'] * 0.9; // Slightly lower confidence for pattern match
                }
            }
            
            // If we found a match, update the item
            if ($projectId) {
                $updateQuery = "UPDATE etsy_order_items 
                               SET project_id = :project_id,
                                   auto_matched = TRUE,
                                   link_confidence = :confidence,
                                   linked_at = NOW()
                               WHERE id = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([
                    ':project_id' => $projectId,
                    ':confidence' => $confidence,
                    ':id' => $itemId
                ]);
                
                // Update mapping statistics
                $statsQuery = "UPDATE etsy_product_mappings 
                              SET times_matched = times_matched + 1,
                                  last_matched_at = NOW()
                              WHERE project_id = :project_id 
                              AND match_type = :match_type";
                $statsStmt = $this->db->prepare($statsQuery);
                $statsStmt->execute([
                    ':project_id' => $projectId,
                    ':match_type' => $matchType
                ]);
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('Error auto-matching item: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update the has_unlinked_items flag on an order
     * 
     * @param int $etsyOrderId The etsy_orders.id
     */
    private function updateOrderUnlinkedFlag($etsyOrderId) {
        try {
            $query = "UPDATE etsy_orders 
                     SET has_unlinked_items = (
                         SELECT COUNT(*) > 0 
                         FROM etsy_order_items 
                         WHERE etsy_order_id = :order_id 
                         AND project_id IS NULL
                     )
                     WHERE id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':order_id' => $etsyOrderId]);
            
        } catch (Exception $e) {
            error_log('Error updating unlinked flag: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a product mapping manually
     * 
     * @param array $data Mapping data
     * @return int|false The mapping ID or false on failure
     */
    public function createProductMapping($data) {
        try {
            $query = "INSERT INTO etsy_product_mappings (
                etsy_listing_id, product_name, product_sku, product_title_pattern,
                project_id, match_type, created_by, confidence, active
            ) VALUES (
                :etsy_listing_id, :product_name, :product_sku, :product_title_pattern,
                :project_id, :match_type, :created_by, :confidence, :active
            )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':etsy_listing_id' => $data['etsy_listing_id'] ?? null,
                ':product_name' => $data['product_name'] ?? null,
                ':product_sku' => $data['product_sku'] ?? null,
                ':product_title_pattern' => $data['product_title_pattern'] ?? null,
                ':project_id' => $data['project_id'],
                ':match_type' => $data['match_type'],
                ':created_by' => $data['created_by'] ?? $_SESSION['username'] ?? 'system',
                ':confidence' => $data['confidence'] ?? 1.00,
                ':active' => $data['active'] ?? true
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log('Error creating product mapping: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all unlinked products (not yet matched to projects)
     * 
     * @return array Unlinked products grouped by name
     */
    public function getUnlinkedProducts() {
        try {
            $query = "SELECT 
                        product_name,
                        product_sku,
                        COUNT(*) as times_ordered,
                        SUM(quantity) as total_quantity,
                        SUM(total_price) as total_revenue,
                        MIN(created_at) as first_ordered,
                        MAX(created_at) as last_ordered
                      FROM etsy_order_items
                      WHERE project_id IS NULL
                      GROUP BY product_name, product_sku
                      ORDER BY times_ordered DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log('Error getting unlinked products: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Link an item to a project manually
     * 
     * @param int $itemId The etsy_order_items.id
     * @param int $projectId The project to link to
     * @param bool $createMapping Whether to create a permanent mapping
     * @return bool Success status
     */
    public function linkItemToProject($itemId, $projectId, $createMapping = false) {
        try {
            // Get the item details
            $query = "SELECT * FROM etsy_order_items WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                return false;
            }
            
            // Update the item
            $updateQuery = "UPDATE etsy_order_items 
                           SET project_id = :project_id,
                               manually_linked = TRUE,
                               link_confidence = 1.00,
                               linked_at = NOW()
                           WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([
                ':project_id' => $projectId,
                ':id' => $itemId
            ]);
            
            // Create permanent mapping if requested
            if ($createMapping && $item['etsy_listing_id']) {
                $this->createProductMapping([
                    'etsy_listing_id' => $item['etsy_listing_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'project_id' => $projectId,
                    'match_type' => 'listing_id',
                    'confidence' => 1.00
                ]);
            }
            
            // Update order's unlinked flag
            $this->updateOrderUnlinkedFlag($item['etsy_order_id']);
            
            return true;
            
        } catch (Exception $e) {
            error_log('Error linking item to project: ' . $e->getMessage());
            return false;
        }
    }
}
?>
