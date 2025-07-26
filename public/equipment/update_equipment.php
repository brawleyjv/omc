<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once realpath(dirname(__FILE__) . '/../../Controllers/EquipmentController.php');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php');
    exit();
}

// Check if equipment ID is provided
if (!isset($_POST['equipment_id']) || empty($_POST['equipment_id'])) {
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('Equipment ID is required.'));
    exit();
}

try {
    $equipmentController = new EquipmentController();
    $equipment_id = $_POST['equipment_id'];
    
    // Prepare the equipment data from form
    $equipment_data = [
        'equipment_name' => $_POST['equipment_name'] ?? '',
        'equipment_type' => $_POST['equipment_type'] ?? '',
        'manufacturer' => $_POST['manufacturer'] ?? '',
        'model_number' => $_POST['model_number'] ?? '',
        'serial_number' => $_POST['serial_number'] ?? '',
        'purchase_date' => $_POST['purchase_date'] ?? null,
        'purchase_price' => $_POST['purchase_price'] ?? null,
        'current_value' => $_POST['current_value'] ?? null,
        'warranty_expiration' => $_POST['warranty_expiration'] ?? null,
        'last_maintenance_date' => $_POST['last_maintenance_date'] ?? null,
        'next_maintenance_date' => $_POST['next_maintenance_date'] ?? null,
        'maintenance_interval_days' => $_POST['maintenance_interval_days'] ?? 365,
        'operating_hours' => $_POST['operating_hours'] ?? 0.00,
        'power_consumption' => $_POST['power_consumption'] ?? '',
        'dimensions' => $_POST['dimensions'] ?? '',
        'weight_kg' => $_POST['weight_kg'] ?? null,
        'location' => $_POST['location'] ?? '',
        'status' => $_POST['status'] ?? 'operational',
        'notes' => $_POST['notes'] ?? ''
    ];
    
    // Update the equipment
    $result = $equipmentController->updateEquipment($equipment_id, $equipment_data);
    
    if ($result['success']) {
        // Success - redirect to equipment list with success message
        header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?success=' . urlencode($result['message']));
        exit();
    } else {
        // Error - redirect back to edit form with error message
        header('Location: ' . BASE_URL . 'Views/equipment/edit_equipment.php?id=' . $equipment_id . '&error=' . urlencode($result['message']));
        exit();
    }
    
} catch (Exception $e) {
    error_log("Error in update_equipment.php: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('An unexpected error occurred. Please try again.'));
    exit();
}
?>
