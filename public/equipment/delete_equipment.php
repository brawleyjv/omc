<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once realpath(dirname(__FILE__) . '/../../Controllers/EquipmentController.php');

// Check if equipment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('Equipment ID is required.'));
    exit();
}

try {
    $equipmentController = new EquipmentController();
    $equipment_id = $_GET['id'];
    
    // Delete the equipment
    $result = $equipmentController->deleteEquipment($equipment_id);
    
    if ($result['success']) {
        // Success - redirect to equipment list with success message
        header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?success=' . urlencode($result['message']));
        exit();
    } else {
        // Error - redirect back to equipment list with error message
        header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode($result['message']));
        exit();
    }
    
} catch (Exception $e) {
    error_log("Error in delete_equipment.php: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('An unexpected error occurred. Please try again.'));
    exit();
}
?>
