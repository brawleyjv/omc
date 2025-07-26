<?php

class EquipmentModel {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    // Get all equipment with optional filtering
    public function getAllEquipment($status = null, $type = null) {
        $sql = "SELECT * FROM equipment WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        if ($type) {
            $sql .= " AND equipment_type = :type";
            $params['type'] = $type;
        }
        
        $sql .= " ORDER BY equipment_name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get equipment by ID
    public function getEquipmentById($id) {
        $sql = "SELECT * FROM equipment WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Add new equipment
    public function addEquipment($data) {
        $sql = "INSERT INTO equipment (
            equipment_name, equipment_type, manufacturer, model_number, 
            serial_number, purchase_date, purchase_price, current_value,
            warranty_expiration, last_maintenance_date, next_maintenance_date,
            maintenance_interval_days, operating_hours, power_consumption,
            dimensions, weight_kg, location, status, notes, image_path
        ) VALUES (
            :equipment_name, :equipment_type, :manufacturer, :model_number,
            :serial_number, :purchase_date, :purchase_price, :current_value,
            :warranty_expiration, :last_maintenance_date, :next_maintenance_date,
            :maintenance_interval_days, :operating_hours, :power_consumption,
            :dimensions, :weight_kg, :location, :status, :notes, :image_path
        )";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }
    
    // Update equipment
    public function updateEquipment($id, $data) {
        $sql = "UPDATE equipment SET 
            equipment_name = :equipment_name,
            equipment_type = :equipment_type,
            manufacturer = :manufacturer,
            model_number = :model_number,
            serial_number = :serial_number,
            purchase_date = :purchase_date,
            purchase_price = :purchase_price,
            current_value = :current_value,
            warranty_expiration = :warranty_expiration,
            last_maintenance_date = :last_maintenance_date,
            next_maintenance_date = :next_maintenance_date,
            maintenance_interval_days = :maintenance_interval_days,
            operating_hours = :operating_hours,
            power_consumption = :power_consumption,
            dimensions = :dimensions,
            weight_kg = :weight_kg,
            location = :location,
            status = :status,
            notes = :notes,
            image_path = :image_path,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }
    
    // Delete equipment
    public function deleteEquipment($id) {
        $sql = "DELETE FROM equipment WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // Search equipment
    public function searchEquipment($search_term) {
        $sql = "SELECT * FROM equipment 
                WHERE equipment_name LIKE :search_term 
                OR equipment_type LIKE :search_term 
                OR manufacturer LIKE :search_term 
                OR model_number LIKE :search_term 
                OR serial_number LIKE :search_term 
                OR location LIKE :search_term
                ORDER BY equipment_name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['search_term' => '%' . $search_term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get equipment types for dropdown
    public function getEquipmentTypes() {
        $sql = "SELECT DISTINCT equipment_type FROM equipment ORDER BY equipment_type ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Get equipment that needs maintenance
    public function getMaintenanceDue($days_ahead = 30) {
        $sql = "SELECT * FROM equipment 
                WHERE next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL :days_ahead DAY)
                AND status != 'retired'
                ORDER BY next_maintenance_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['days_ahead' => $days_ahead]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update operating hours
    public function updateOperatingHours($id, $hours) {
        $sql = "UPDATE equipment SET 
                operating_hours = :hours, 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['id' => $id, 'hours' => $hours]);
    }
    
    // Get equipment statistics
    public function getEquipmentStats() {
        $stats = [];
        
        // Total equipment count
        $sql = "SELECT COUNT(*) as total FROM equipment";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Equipment by status
        $sql = "SELECT status, COUNT(*) as count FROM equipment GROUP BY status";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Total value
        $sql = "SELECT SUM(current_value) as total_value FROM equipment WHERE status != 'retired'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stats['total_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'];
        
        return $stats;
    }
}
