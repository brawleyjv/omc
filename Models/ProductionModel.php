<?php
namespace MyApp\Models;

use PDO;
use PDOException;
use Exception;

/**
 * ProductionModel
 * 
 * Handles production batch recording, inventory management, and cost tracking
 */
class ProductionModel {
    private $db;
    
    public function __construct(PDO $database) {
        $this->db = $database;
    }
    
    /**
     * Generate next batch number for a given date
     * Format: YYYYMMDD-N (e.g., 20251219-1, 20251219-2)
     * 
     * @param string $date Date in Y-m-d format
     * @return string Next batch number
     */
    public function generateBatchNumber($date) {
        try {
            // Format: YYYYMMDD
            $datePrefix = date('Ymd', strtotime($date));
            
            // Find highest sequence number for this date
            $query = "SELECT batch_number 
                     FROM production_batches 
                     WHERE batch_number LIKE :prefix 
                     ORDER BY batch_number DESC 
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':prefix' => $datePrefix . '-%']);
            $lastBatch = $stmt->fetchColumn();
            
            if ($lastBatch) {
                // Extract sequence number and increment
                $parts = explode('-', $lastBatch);
                $sequence = isset($parts[1]) ? (int)$parts[1] + 1 : 1;
            } else {
                // First batch for this date
                $sequence = 1;
            }
            
            return $datePrefix . '-' . $sequence;
            
        } catch (PDOException $e) {
            error_log('Error generating batch number: ' . $e->getMessage());
            // Fallback to simple format
            return date('Ymd', strtotime($date)) . '-1';
        }
    }
    
    /**
     * Record a new production batch
     * 
     * @param array $data Batch data (project_id, quantity, date, costs, notes)
     * @return int|false Batch ID on success, false on failure
     */
    public function recordProductionBatch($data) {
        try {
            $this->db->beginTransaction();
            
            // Auto-generate batch number if not provided
            if (empty($data['batch_number'])) {
                $data['batch_number'] = $this->generateBatchNumber($data['production_date']);
            }
            
            // Calculate cost per unit if costs provided
            $costPerUnit = null;
            if (isset($data['material_cost']) && isset($data['labor_cost']) && $data['quantity_produced'] > 0) {
                $totalCost = $data['material_cost'] + $data['labor_cost'];
                $costPerUnit = $totalCost / $data['quantity_produced'];
            }
            
            // Insert production batch
            $query = "INSERT INTO production_batches (
                        project_id, batch_number, quantity_produced, production_date,
                        labor_hours, laser_time, mill_time, material_cost, labor_cost, cost_per_unit,
                        notes, produced_by, created_at
                      ) VALUES (
                        :project_id, :batch_number, :quantity, :date,
                        :labor_hours, :laser_time, :mill_time, :material_cost, :labor_cost, :cost_per_unit,
                        :notes, :produced_by, NOW()
                      )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':project_id' => $data['project_id'],
                ':batch_number' => $data['batch_number'] ?? null,
                ':quantity' => $data['quantity_produced'],
                ':date' => $data['production_date'],
                ':labor_hours' => $data['labor_hours'] ?? null,
                ':laser_time' => $data['laser_time'] ?? null,
                ':mill_time' => $data['mill_time'] ?? null,
                ':material_cost' => $data['material_cost'] ?? null,
                ':labor_cost' => $data['labor_cost'] ?? null,
                ':cost_per_unit' => $costPerUnit,
                ':notes' => $data['notes'] ?? null,
                ':produced_by' => $data['produced_by'] ?? null
            ]);
            
            $batchId = $this->db->lastInsertId();
            
            // Update project inventory
            $this->increaseInventory(
                $data['project_id'],
                $data['quantity_produced'],
                'production_batch',
                $batchId,
                'Production batch ' . ($data['batch_number'] ?? $batchId)
            );
            
            // Update project's average cost per unit
            if ($costPerUnit !== null) {
                $this->updateProjectCostPerUnit($data['project_id']);
            }
            
            $this->db->commit();
            return $batchId;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error recording production batch: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Increase inventory quantity
     * 
     * @param int $projectId
     * @param int $quantity Amount to increase
     * @param string $referenceType Source of increase
     * @param int $referenceId ID of source record
     * @param string $notes Optional notes
     * @return bool Success
     */
    public function increaseInventory($projectId, $quantity, $referenceType, $referenceId, $notes = null) {
        try {
            // Get current inventory
            $current = $this->getCurrentInventory($projectId);
            $newQuantity = $current + $quantity;
            
            // Create transaction record
            $query = "INSERT INTO inventory_transactions (
                        project_id, transaction_type, quantity,
                        quantity_before, quantity_after,
                        reference_type, reference_id, notes, created_at
                      ) VALUES (
                        :project_id, 'production', :quantity,
                        :qty_before, :qty_after,
                        :ref_type, :ref_id, :notes, NOW()
                      )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':project_id' => $projectId,
                ':quantity' => $quantity,
                ':qty_before' => $current,
                ':qty_after' => $newQuantity,
                ':ref_type' => $referenceType,
                ':ref_id' => $referenceId,
                ':notes' => $notes
            ]);
            
            // Update project inventory
            $updateQuery = "UPDATE projects 
                           SET inventory_quantity = :quantity 
                           WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([
                ':quantity' => $newQuantity,
                ':id' => $projectId
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log('Error increasing inventory: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrease inventory quantity (for sales, damage, etc)
     * 
     * @param int $projectId
     * @param int $quantity Amount to decrease
     * @param string $transactionType Type: sale, damage, adjustment
     * @param string $referenceType Source of decrease
     * @param int $referenceId ID of source record
     * @param string $notes Optional notes
     * @return bool Success
     */
    public function decreaseInventory($projectId, $quantity, $transactionType, $referenceType, $referenceId, $notes = null) {
        try {
            // Get current inventory
            $current = $this->getCurrentInventory($projectId);
            $newQuantity = max(0, $current - $quantity); // Don't go below 0
            
            // Create transaction record
            $query = "INSERT INTO inventory_transactions (
                        project_id, transaction_type, quantity,
                        quantity_before, quantity_after,
                        reference_type, reference_id, notes, created_at
                      ) VALUES (
                        :project_id, :trans_type, :quantity,
                        :qty_before, :qty_after,
                        :ref_type, :ref_id, :notes, NOW()
                      )";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':project_id' => $projectId,
                ':trans_type' => $transactionType,
                ':quantity' => -$quantity, // Negative for decrease
                ':qty_before' => $current,
                ':qty_after' => $newQuantity,
                ':ref_type' => $referenceType,
                ':ref_id' => $referenceId,
                ':notes' => $notes
            ]);
            
            // Update project inventory
            $updateQuery = "UPDATE projects 
                           SET inventory_quantity = :quantity 
                           WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([
                ':quantity' => $newQuantity,
                ':id' => $projectId
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log('Error decreasing inventory: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current inventory quantity for a project
     * 
     * @param int $projectId
     * @return int Current inventory
     */
    public function getCurrentInventory($projectId) {
        $query = "SELECT inventory_quantity FROM projects WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $projectId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['inventory_quantity'] : 0;
    }
    
    /**
     * Update project's average cost per unit based on production batches
     * 
     * @param int $projectId
     * @return bool Success
     */
    public function updateProjectCostPerUnit($projectId) {
        try {
            // Calculate weighted average cost from recent batches (last 10)
            $query = "SELECT AVG(cost_per_unit) as avg_cost
                     FROM production_batches
                     WHERE project_id = :id 
                       AND cost_per_unit IS NOT NULL
                     ORDER BY production_date DESC
                     LIMIT 10";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $projectId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['avg_cost']) {
                $updateQuery = "UPDATE projects 
                               SET cost_per_unit = :cost 
                               WHERE id = :id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([
                    ':cost' => $result['avg_cost'],
                    ':id' => $projectId
                ]);
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log('Error updating cost per unit: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all production batches for a project
     * 
     * @param int $projectId
     * @param int $limit Optional limit
     * @return array Production batches
     */
    public function getProjectBatches($projectId, $limit = null) {
        $query = "SELECT * FROM production_batches 
                 WHERE project_id = :id 
                 ORDER BY production_date DESC, created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get inventory transactions for a project
     * 
     * @param int $projectId
     * @param int $limit Optional limit
     * @return array Transactions
     */
    public function getInventoryTransactions($projectId, $limit = null) {
        $query = "SELECT * FROM inventory_transactions 
                 WHERE project_id = :id 
                 ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get projects with low stock (at or below reorder point)
     * 
     * @return array Projects needing restock
     */
    public function getLowStockProjects() {
        $query = "SELECT 
                    id, project_name, production_status,
                    inventory_quantity, reorder_point, batch_size,
                    (reorder_point - inventory_quantity) as units_needed
                  FROM projects
                  WHERE production_status IN ('ready', 'active')
                    AND inventory_quantity <= reorder_point
                  ORDER BY units_needed DESC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get inventory dashboard summary
     * 
     * @return array Summary statistics
     */
    public function getInventorySummary() {
        $query = "SELECT 
                    production_status,
                    COUNT(*) as project_count,
                    SUM(inventory_quantity) as total_units,
                    SUM(inventory_quantity * IFNULL(cost_per_unit, 0)) as inventory_value
                  FROM projects
                  WHERE production_status IN ('ready', 'active')
                  GROUP BY production_status";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update project production status
     * 
     * @param int $projectId
     * @param string $status design|ready|active|discontinued
     * @return bool Success
     */
    public function updateProductionStatus($projectId, $status) {
        try {
            $query = "UPDATE projects SET production_status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':status' => $status,
                ':id' => $projectId
            ]);
        } catch (PDOException $e) {
            error_log('Error updating production status: ' . $e->getMessage());
            return false;
        }
    }
}
