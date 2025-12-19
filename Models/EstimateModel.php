<?php
require_once realpath(dirname(__FILE__) . '/../config.php');

class EstimateModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Generate unique estimate number
     */
    public function generateEstimateNumber() {
        $year = date('Y');
        $query = "SELECT COUNT(*) as count FROM estimates WHERE YEAR(created_at) = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] + 1;
        return 'EST-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate estimate totals using the formula:
     * (materials_cost / 0.3) + ((labor_time * hourly_rate) / 0.2)
     */
    public function calculateEstimate($materialsData, $routerTime, $laserTime, $laborHours, $customItems = []) {
        // Get rates from setup table
        $setupQuery = "SELECT mill_rate, laser_rate, labor_rate FROM setup LIMIT 1";
        $setupStmt = $this->db->prepare($setupQuery);
        $setupStmt->execute();
        $rates = $setupStmt->fetch(PDO::FETCH_ASSOC);
        
        $millRate = $rates['mill_rate'] ?? 0.85;
        $laserRate = $rates['laser_rate'] ?? 0.50;
        $laborRate = $rates['labor_rate'] ?? 25.00;

        // Calculate materials cost
        $materialsCost = 0;
        foreach ($materialsData as $material) {
            $materialsCost += $material['total_cost'];
        }

        // Calculate custom items cost
        $customItemsCost = 0;
        foreach ($customItems as $item) {
            $customItemsCost += $item['cost'];
        }

        // Calculate machine costs
        $routerCost = $routerTime * $millRate;
        $laserCost = $laserTime * $laserRate;
        $machineCost = $routerCost + $laserCost;

        // Calculate labor cost
        $laborCost = $laborHours * $laborRate;

        // Total labor time for formula (in hours)
        $totalLaborTime = $laborHours + ($routerTime / 60) + ($laserTime / 60);

        // Apply the formula: (materials_cost / 0.3) + ((labor_time * hourly_rate) / 0.2)
        $materialMarkup = $materialsCost / 0.3;
        $laborMarkup = ($totalLaborTime * $laborRate) / 0.2;
        
        $subtotal = $materialsCost + $machineCost + $laborCost + $customItemsCost;
        $totalEstimate = $materialMarkup + $laborMarkup + $customItemsCost;

        return [
            'materials_cost' => round($materialsCost, 2),
            'machine_cost' => round($machineCost, 2),
            'labor_cost' => round($laborCost, 2),
            'custom_items_cost' => round($customItemsCost, 2),
            'subtotal' => round($subtotal, 2),
            'total_estimate' => round($totalEstimate, 2)
        ];
    }

    /**
     * Create new estimate with materials and custom items
     */
    public function createEstimate($data, $materialsData, $customItems = []) {
        try {
            $this->db->beginTransaction();

            // Calculate totals
            $calculations = $this->calculateEstimate(
                $materialsData, 
                $data['router_time'], 
                $data['laser_time'], 
                $data['labor_hours'],
                $customItems
            );

            // Generate estimate number
            $estimateNumber = $this->generateEstimateNumber();

            // Insert estimate
            $query = "INSERT INTO estimates (
                estimate_number, customer_name, customer_email, customer_phone,
                project_name, project_description, router_time, laser_time, labor_hours,
                materials_cost, labor_cost, machine_cost, subtotal, total_estimate,
                status, notes, created_by
            ) VALUES (
                :estimate_number, :customer_name, :customer_email, :customer_phone,
                :project_name, :project_description, :router_time, :laser_time, :labor_hours,
                :materials_cost, :labor_cost, :machine_cost, :subtotal, :total_estimate,
                :status, :notes, :created_by
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':estimate_number' => $estimateNumber,
                ':customer_name' => $data['customer_name'],
                ':customer_email' => $data['customer_email'] ?? null,
                ':customer_phone' => $data['customer_phone'] ?? null,
                ':project_name' => $data['project_name'],
                ':project_description' => $data['project_description'] ?? null,
                ':router_time' => $data['router_time'],
                ':laser_time' => $data['laser_time'],
                ':labor_hours' => $data['labor_hours'],
                ':materials_cost' => $calculations['materials_cost'],
                ':labor_cost' => $calculations['labor_cost'],
                ':machine_cost' => $calculations['machine_cost'],
                ':subtotal' => $calculations['subtotal'],
                ':total_estimate' => $calculations['total_estimate'],
                ':status' => $data['status'] ?? 'draft',
                ':notes' => $data['notes'] ?? null,
                ':created_by' => $data['created_by'] ?? null
            ]);

            $estimateId = $this->db->lastInsertId();

            // Insert materials
            if (!empty($materialsData)) {
                $materialQuery = "INSERT INTO estimate_materials (
                    estimate_id, material_name, quantity, unit_type, unit_cost, total_cost, notes, sort_order
                ) VALUES (
                    :estimate_id, :material_name, :quantity, :unit_type, :unit_cost, :total_cost, :notes, :sort_order
                )";
                $materialStmt = $this->db->prepare($materialQuery);

                foreach ($materialsData as $index => $material) {
                    $materialStmt->execute([
                        ':estimate_id' => $estimateId,
                        ':material_name' => $material['material_name'],
                        ':quantity' => $material['quantity'],
                        ':unit_type' => $material['unit_type'],
                        ':unit_cost' => $material['unit_cost'],
                        ':total_cost' => $material['total_cost'],
                        ':notes' => $material['notes'] ?? null,
                        ':sort_order' => $index
                    ]);
                }
            }

            // Insert custom items
            if (!empty($customItems)) {
                $customQuery = "INSERT INTO estimate_custom_items (
                    estimate_id, item_name, description, cost, sort_order
                ) VALUES (
                    :estimate_id, :item_name, :description, :cost, :sort_order
                )";
                $customStmt = $this->db->prepare($customQuery);

                foreach ($customItems as $index => $item) {
                    $customStmt->execute([
                        ':estimate_id' => $estimateId,
                        ':item_name' => $item['item_name'],
                        ':description' => $item['description'] ?? null,
                        ':cost' => $item['cost'],
                        ':sort_order' => $index
                    ]);
                }
            }

            $this->db->commit();
            return ['success' => true, 'estimate_id' => $estimateId, 'estimate_number' => $estimateNumber];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating estimate: " . $e->getMessage());
            throw new \Exception("Failed to create estimate: " . $e->getMessage());
        }
    }

    /**
     * Get all estimates
     */
    public function getAllEstimates() {
        $query = "SELECT * FROM estimates ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get estimate by ID with materials and custom items
     */
    public function getEstimateById($estimateId) {
        $query = "SELECT * FROM estimates WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $estimateId);
        $stmt->execute();
        $estimate = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estimate) {
            // Get materials
            $materialQuery = "SELECT * FROM estimate_materials WHERE estimate_id = :estimate_id ORDER BY sort_order";
            $materialStmt = $this->db->prepare($materialQuery);
            $materialStmt->bindParam(':estimate_id', $estimateId);
            $materialStmt->execute();
            $estimate['materials'] = $materialStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get custom items
            $customQuery = "SELECT * FROM estimate_custom_items WHERE estimate_id = :estimate_id ORDER BY sort_order";
            $customStmt = $this->db->prepare($customQuery);
            $customStmt->bindParam(':estimate_id', $estimateId);
            $customStmt->execute();
            $estimate['custom_items'] = $customStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $estimate;
    }

    /**
     * Update estimate status
     */
    public function updateStatus($estimateId, $status) {
        $query = "UPDATE estimates SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':status' => $status, ':id' => $estimateId]);
    }

    /**
     * Update existing estimate
     */
    public function updateEstimate($estimateId, $data, $materialsData, $customItems = []) {
        try {
            $this->db->beginTransaction();

            // Calculate totals
            $calculations = $this->calculateEstimate(
                $materialsData, 
                $data['router_time'], 
                $data['laser_time'], 
                $data['labor_hours'],
                $customItems
            );

            // Update estimate
            $query = "UPDATE estimates SET
                customer_name = :customer_name,
                customer_email = :customer_email,
                customer_phone = :customer_phone,
                project_name = :project_name,
                project_description = :project_description,
                router_time = :router_time,
                laser_time = :laser_time,
                labor_hours = :labor_hours,
                materials_cost = :materials_cost,
                labor_cost = :labor_cost,
                machine_cost = :machine_cost,
                subtotal = :subtotal,
                total_estimate = :total_estimate,
                notes = :notes
                WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':customer_name' => $data['customer_name'],
                ':customer_email' => $data['customer_email'] ?? null,
                ':customer_phone' => $data['customer_phone'] ?? null,
                ':project_name' => $data['project_name'],
                ':project_description' => $data['project_description'] ?? null,
                ':router_time' => $data['router_time'],
                ':laser_time' => $data['laser_time'],
                ':labor_hours' => $data['labor_hours'],
                ':materials_cost' => $calculations['materials_cost'],
                ':labor_cost' => $calculations['labor_cost'],
                ':machine_cost' => $calculations['machine_cost'],
                ':subtotal' => $calculations['subtotal'],
                ':total_estimate' => $calculations['total_estimate'],
                ':notes' => $data['notes'] ?? null,
                ':id' => $estimateId
            ]);

            // Delete existing materials and custom items
            $this->db->prepare("DELETE FROM estimate_materials WHERE estimate_id = :id")->execute([':id' => $estimateId]);
            $this->db->prepare("DELETE FROM estimate_custom_items WHERE estimate_id = :id")->execute([':id' => $estimateId]);

            // Insert updated materials
            if (!empty($materialsData)) {
                $materialQuery = "INSERT INTO estimate_materials (
                    estimate_id, material_name, quantity, unit_type, unit_cost, total_cost, notes, sort_order
                ) VALUES (
                    :estimate_id, :material_name, :quantity, :unit_type, :unit_cost, :total_cost, :notes, :sort_order
                )";
                $materialStmt = $this->db->prepare($materialQuery);

                foreach ($materialsData as $index => $material) {
                    $materialStmt->execute([
                        ':estimate_id' => $estimateId,
                        ':material_name' => $material['material_name'],
                        ':quantity' => $material['quantity'],
                        ':unit_type' => $material['unit_type'],
                        ':unit_cost' => $material['unit_cost'],
                        ':total_cost' => $material['total_cost'],
                        ':notes' => $material['notes'] ?? null,
                        ':sort_order' => $index
                    ]);
                }
            }

            // Insert updated custom items
            if (!empty($customItems)) {
                $customQuery = "INSERT INTO estimate_custom_items (
                    estimate_id, item_name, description, cost, sort_order
                ) VALUES (
                    :estimate_id, :item_name, :description, :cost, :sort_order
                )";
                $customStmt = $this->db->prepare($customQuery);

                foreach ($customItems as $index => $item) {
                    $customStmt->execute([
                        ':estimate_id' => $estimateId,
                        ':item_name' => $item['item_name'],
                        ':description' => $item['description'] ?? null,
                        ':cost' => $item['cost'],
                        ':sort_order' => $index
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating estimate: " . $e->getMessage());
            throw new \Exception("Failed to update estimate: " . $e->getMessage());
        }
    }

    /**
     * Delete estimate
     */
    public function deleteEstimate($estimateId) {
        $query = "DELETE FROM estimates WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $estimateId]);
    }
}
?>