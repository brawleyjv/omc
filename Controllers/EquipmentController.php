<?php
require_once __DIR__ . '/../config.php';
require_once realpath(dirname(__FILE__) . '/../Models/EquipmentModel.php');

class EquipmentController {
    private $equipmentModel;
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->equipmentModel = new EquipmentModel($this->conn);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    // Handle equipment listing with filters
    public function listEquipment($status = null, $type = null) {
        try {
            return $this->equipmentModel->getAllEquipment($status, $type);
        } catch (Exception $e) {
            error_log("Error listing equipment: " . $e->getMessage());
            return false;
        }
    }
    
    // Handle adding new equipment
    public function addEquipment($data) {
        try {
            // Validate required fields
            if (empty($data['equipment_name']) || empty($data['equipment_type'])) {
                throw new Exception("Equipment name and type are required");
            }
            
            // Handle file upload if provided
            if (isset($_FILES['equipment_image']) && $_FILES['equipment_image']['error'] === UPLOAD_ERR_OK) {
                $data['image_path'] = $this->handleImageUpload($_FILES['equipment_image']);
            }
            
            // Set default values
            $defaults = [
                'manufacturer' => '',
                'model_number' => '',
                'serial_number' => '',
                'purchase_date' => null,
                'purchase_price' => null,
                'current_value' => null,
                'warranty_expiration' => null,
                'last_maintenance_date' => null,
                'next_maintenance_date' => null,
                'maintenance_interval_days' => 365,
                'operating_hours' => 0.00,
                'power_consumption' => '',
                'dimensions' => '',
                'weight_kg' => null,
                'location' => '',
                'status' => 'operational',
                'notes' => '',
                'image_path' => null
            ];
            
            // Merge with defaults
            $equipment_data = array_merge($defaults, $data);
            
            // Convert empty strings to null for numeric/date fields
            $numeric_fields = ['purchase_price', 'current_value', 'weight_kg', 'operating_hours', 'maintenance_interval_days'];
            $date_fields = ['purchase_date', 'warranty_expiration', 'last_maintenance_date', 'next_maintenance_date'];
            
            foreach ($numeric_fields as $field) {
                if ($equipment_data[$field] === '') {
                    $equipment_data[$field] = null;
                }
            }
            
            foreach ($date_fields as $field) {
                if ($equipment_data[$field] === '') {
                    $equipment_data[$field] = null;
                }
            }
            
            $result = $this->equipmentModel->addEquipment($equipment_data);
            
            if ($result) {
                return ['success' => true, 'message' => 'Equipment added successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to add equipment'];
            }
            
        } catch (Exception $e) {
            error_log("Error adding equipment: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Handle equipment search
    public function searchEquipment($search_term) {
        try {
            return $this->equipmentModel->searchEquipment($search_term);
        } catch (Exception $e) {
            error_log("Error searching equipment: " . $e->getMessage());
            return false;
        }
    }
    
    // Get equipment by ID
    public function getEquipmentById($id) {
        try {
            return $this->equipmentModel->getEquipmentById($id);
        } catch (Exception $e) {
            error_log("Error getting equipment: " . $e->getMessage());
            return false;
        }
    }
    
    // Handle equipment update
    public function updateEquipment($id, $data) {
        try {
            // Validate required fields
            if (empty($data['equipment_name']) || empty($data['equipment_type'])) {
                throw new Exception("Equipment name and type are required");
            }
            
            // Handle file upload if provided
            if (isset($_FILES['equipment_image']) && $_FILES['equipment_image']['error'] === UPLOAD_ERR_OK) {
                $data['image_path'] = $this->handleImageUpload($_FILES['equipment_image']);
            }
            
            // Convert empty strings to null for numeric/date fields
            $numeric_fields = ['purchase_price', 'current_value', 'weight_kg', 'operating_hours', 'maintenance_interval_days'];
            $date_fields = ['purchase_date', 'warranty_expiration', 'last_maintenance_date', 'next_maintenance_date'];
            
            foreach ($numeric_fields as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            foreach ($date_fields as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            
            $result = $this->equipmentModel->updateEquipment($id, $data);
            
            if ($result) {
                return ['success' => true, 'message' => 'Equipment updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update equipment'];
            }
            
        } catch (Exception $e) {
            error_log("Error updating equipment: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Handle equipment deletion
    public function deleteEquipment($id) {
        try {
            $result = $this->equipmentModel->deleteEquipment($id);
            
            if ($result) {
                return ['success' => true, 'message' => 'Equipment deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete equipment'];
            }
            
        } catch (Exception $e) {
            error_log("Error deleting equipment: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Get equipment types for dropdown
    public function getEquipmentTypes() {
        try {
            return $this->equipmentModel->getEquipmentTypes();
        } catch (Exception $e) {
            error_log("Error getting equipment types: " . $e->getMessage());
            return [];
        }
    }
    
    // Get maintenance due
    public function getMaintenanceDue($days_ahead = 30) {
        try {
            return $this->equipmentModel->getMaintenanceDue($days_ahead);
        } catch (Exception $e) {
            error_log("Error getting maintenance due: " . $e->getMessage());
            return [];
        }
    }
    
    // Get equipment statistics
    public function getEquipmentStats() {
        try {
            return $this->equipmentModel->getEquipmentStats();
        } catch (Exception $e) {
            error_log("Error getting equipment stats: " . $e->getMessage());
            return [];
        }
    }
    
    // Handle image upload
    private function handleImageUpload($file) {
        $upload_dir = realpath(dirname(__FILE__) . '/../Views/equipment/images/');
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
        }
        
        if ($file['size'] > $max_size) {
            throw new Exception("File size too large. Maximum size is 5MB.");
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'equipment_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $upload_path = $upload_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return 'Views/equipment/images/' . $filename;
        } else {
            throw new Exception("Failed to upload image");
        }
    }
    
    public function __destruct() {
        $this->conn = null;
    }
}
