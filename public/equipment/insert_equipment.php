<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';
require_once realpath(dirname(__FILE__) . '/../../Controllers/EquipmentController.php');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'Views/equipment/add_equipment.php');
    exit();
}

try {
    $equipmentController = new EquipmentController();
    
    // Debug: Log the received POST data
    error_log("POST data received: " . print_r($_POST, true));
    
    // Check if required fields are actually present and not empty
    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $equipment_type = trim($_POST['type'] ?? '');
    
    error_log("Equipment name: '{$equipment_name}', Equipment type: '{$equipment_type}'");
    
    if (empty($equipment_name) || empty($equipment_type)) {
        error_log("Validation failed - Name: " . (empty($equipment_name) ? 'empty' : 'not empty') . ", Type: " . (empty($equipment_type) ? 'empty' : 'not empty'));
        $_SESSION['form_data'] = $_POST;
        header('Location: ' . BASE_URL . 'Views/equipment/add_equipment.php?error=' . urlencode('Equipment name and type are required. Please fill in both fields.'));
        exit();
    }
    
    // Prepare the equipment data from form
    $equipment_data = [
        'equipment_name' => $equipment_name,
        'equipment_type' => $equipment_type,
        'manufacturer' => $_POST['manufacturer'] ?? '',
        'model_number' => $_POST['model'] ?? '',  // Form field is named 'model'
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
        'notes' => $_POST['description'] ?? ''  // Form field is named 'description'
    ];
    
    // Debug: Log the prepared equipment data
    error_log("Equipment data prepared: " . print_r($equipment_data, true));
    
    // Add the equipment
    $result = $equipmentController->addEquipment($equipment_data);
    
    if ($result['success']) {
        // Success - redirect to equipment list with success message
        header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?success=' . urlencode($result['message']));
        exit();
    } else {
        // Store form data in session for preservation
        $_SESSION['form_data'] = $_POST;
        // Error - redirect back to form with error message
        header('Location: ' . BASE_URL . 'Views/equipment/add_equipment.php?error=' . urlencode($result['message']));
        exit();
    }
    
} catch (Exception $e) {
    // Store form data in session for preservation
    $_SESSION['form_data'] = $_POST;
    error_log("Error in insert_equipment.php: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'Views/equipment/add_equipment.php?error=' . urlencode('An unexpected error occurred. Please try again.'));
    exit();
}
?>
