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

        // Apply the formula: (materials_cost / 0.3) + ((labor_hours * hourly_rate) / 0.2) + machine_cost (no markup) + custom_items (no markup)
        $materialMarkup = $materialsCost / 0.3;
        $laborMarkup = ($laborHours * $laborRate) / 0.2;
        
        $subtotal = $materialsCost + $machineCost + $laborCost + $customItemsCost;
        $totalEstimate = $materialMarkup + $laborMarkup + $machineCost + $customItemsCost;

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

    /**
     * Create estimate from project data
     * @param int $projectId - Project ID to link
     * @param array $projectData - Project data (times, name, description)
     * @param array $materialsData - Materials array
     * @param array $customItems - Custom items array
     * @param bool $isProjectEstimate - Whether this is a project estimate (no customer)
     * @return array - Result with estimate_id and estimate_number
     */
    public function createEstimateFromProject($projectId, $projectData, $materialsData, $customItems = [], $isProjectEstimate = true) {
        try {
            $this->db->beginTransaction();

            // Calculate totals
            $calculations = $this->calculateEstimate(
                $materialsData,
                $projectData['router_time'] ?? 0,
                $projectData['laser_time'] ?? 0,
                $projectData['labor_hours'] ?? 0,
                $customItems
            );

            // Generate estimate number
            $estimateNumber = $this->generateEstimateNumber();

            // Insert estimate with project linkage
            $query = "INSERT INTO estimates (
                estimate_number, customer_name, customer_email, customer_phone,
                project_name, project_description, router_time, laser_time, labor_hours,
                materials_cost, labor_cost, machine_cost, subtotal, total_estimate,
                status, notes, created_by, project_id, is_project_estimate
            ) VALUES (
                :estimate_number, :customer_name, :customer_email, :customer_phone,
                :project_name, :project_description, :router_time, :laser_time, :labor_hours,
                :materials_cost, :labor_cost, :machine_cost, :subtotal, :total_estimate,
                :status, :notes, :created_by, :project_id, :is_project_estimate
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':estimate_number' => $estimateNumber,
                ':customer_name' => $projectData['customer_name'] ?? null,
                ':customer_email' => $projectData['customer_email'] ?? null,
                ':customer_phone' => $projectData['customer_phone'] ?? null,
                ':project_name' => $projectData['project_name'],
                ':project_description' => $projectData['project_description'] ?? null,
                ':router_time' => $projectData['router_time'] ?? 0,
                ':laser_time' => $projectData['laser_time'] ?? 0,
                ':labor_hours' => $projectData['labor_hours'] ?? 0,
                ':materials_cost' => $calculations['materials_cost'],
                ':labor_cost' => $calculations['labor_cost'],
                ':machine_cost' => $calculations['machine_cost'],
                ':subtotal' => $calculations['subtotal'],
                ':total_estimate' => $calculations['total_estimate'],
                ':status' => 'draft',
                ':notes' => $projectData['notes'] ?? null,
                ':created_by' => $projectData['created_by'] ?? null,
                ':project_id' => $projectId,
                ':is_project_estimate' => $isProjectEstimate ? 1 : 0
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

            // Update project with estimate_id
            $updateProjectQuery = "UPDATE projects SET estimate_id = :estimate_id WHERE id = :project_id";
            $updateProjectStmt = $this->db->prepare($updateProjectQuery);
            $updateProjectStmt->execute([
                ':estimate_id' => $estimateId,
                ':project_id' => $projectId
            ]);

            $this->db->commit();
            return ['success' => true, 'estimate_id' => $estimateId, 'estimate_number' => $estimateNumber];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating estimate from project: " . $e->getMessage());
            throw new \Exception("Failed to create estimate from project: " . $e->getMessage());
        }
    }

    /**
     * Get estimate for a specific project
     */
    public function getEstimateByProjectId($projectId) {
        $query = "SELECT * FROM estimates WHERE project_id = :project_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();
        $estimate = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estimate) {
            // Get materials
            $materialQuery = "SELECT * FROM estimate_materials WHERE estimate_id = :estimate_id ORDER BY sort_order";
            $materialStmt = $this->db->prepare($materialQuery);
            $materialStmt->bindParam(':estimate_id', $estimate['id']);
            $materialStmt->execute();
            $estimate['materials'] = $materialStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get custom items
            $customQuery = "SELECT * FROM estimate_custom_items WHERE estimate_id = :estimate_id ORDER BY sort_order";
            $customStmt = $this->db->prepare($customQuery);
            $customStmt->bindParam(':estimate_id', $estimate['id']);
            $customStmt->execute();
            $estimate['custom_items'] = $customStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $estimate;
    }

    /**
     * Convert project estimate to customer estimate
     */
    public function convertToCustomerEstimate($estimateId, $customerName, $customerEmail = null, $customerPhone = null) {
        $query = "UPDATE estimates SET 
            customer_name = :customer_name,
            customer_email = :customer_email,
            customer_phone = :customer_phone,
            is_project_estimate = 0
            WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':customer_name' => $customerName,
            ':customer_email' => $customerEmail,
            ':customer_phone' => $customerPhone,
            ':id' => $estimateId
        ]);
    }
}
?>