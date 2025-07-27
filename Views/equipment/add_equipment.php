<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

// Get success or error messages from URL parameters
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// Preserve form data on error (from session or previous submission)
$form_data = [
    'equipment_name' => $_SESSION['form_data']['equipment_name'] ?? '',
    'type' => $_SESSION['form_data']['type'] ?? '',
    'manufacturer' => $_SESSION['form_data']['manufacturer'] ?? '',
    'model' => $_SESSION['form_data']['model'] ?? '',
    'serial_number' => $_SESSION['form_data']['serial_number'] ?? '',
    'purchase_date' => $_SESSION['form_data']['purchase_date'] ?? '',
    'purchase_price' => $_SESSION['form_data']['purchase_price'] ?? '',
    'status' => $_SESSION['form_data']['status'] ?? 'operational',
    'location' => $_SESSION['form_data']['location'] ?? '',
    'description' => $_SESSION['form_data']['description'] ?? '',
    'maintenance_notes' => $_SESSION['form_data']['maintenance_notes'] ?? ''
];

// Clear form data from session after use
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Add New Equipment</h1>
                <p class="text-muted">Add tools, machinery, or equipment to your inventory</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/equipment/index.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Equipment Menu
                </a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="btn btn-secondary">
                    <span class="icon">📋</span>
                    View All Equipment
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="notification notification-success">
                <p><strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="notification notification-error">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Equipment Information</h2>
                <p class="text-muted">Enter the details for the new equipment item</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>public/equipment/insert_equipment.php" method="post" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="equipment_name" class="form-label required">Equipment Name</label>
                            <input type="text" id="equipment_name" name="equipment_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['equipment_name']); ?>"
                                   placeholder="Enter equipment name...">
                            <small class="form-text">The name or description of the equipment</small>
                        </div>

                        <div class="form-group">
                            <label for="type" class="form-label required">Equipment Type</label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="">Select equipment type...</option>
                                <option value="Power Tool">Power Tool</option>
                                <option value="Hand Tool">Hand Tool</option>
                                <option value="Machinery">Machinery</option>
                                <option value="Measuring Tool">Measuring Tool</option>
                                <option value="Safety Equipment">Safety Equipment</option>
                                <option value="Woodworking">Woodworking</option>
                                <option value="Metalworking">Metalworking</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Other">Other</option>
                            </select>
                            <small class="form-text">Category of the equipment</small>
                        </div>

                        <div class="form-group">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" id="manufacturer" name="manufacturer" class="form-control"
                                   placeholder="Enter manufacturer name...">
                            <small class="form-text">Who made this equipment</small>
                        </div>

                        <div class="form-group">
                            <label for="model" class="form-label">Model Number</label>
                            <input type="text" id="model" name="model" class="form-control"
                                   placeholder="Enter model number...">
                            <small class="form-text">Model or part number</small>
                        </div>

                        <div class="form-group">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" id="serial_number" name="serial_number" class="form-control"
                                   placeholder="Enter serial number...">
                            <small class="form-text">Unique serial number if available</small>
                        </div>

                        <div class="form-group">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" id="purchase_date" name="purchase_date" class="form-control">
                            <small class="form-text">When was this equipment purchased</small>
                        </div>

                        <div class="form-group">
                            <label for="purchase_price" class="form-label">Purchase Price</label>
                            <input type="number" id="purchase_price" name="purchase_price" class="form-control" 
                                   step="0.01" min="0" placeholder="0.00">
                            <small class="form-text">Original purchase price</small>
                        </div>

                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="Active">Active</option>
                                <option value="Maintenance">In Maintenance</option>
                                <option value="Retired">Retired</option>
                                <option value="Lost">Lost/Missing</option>
                            </select>
                            <small class="form-text">Current status of the equipment</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" id="location" name="location" class="form-control"
                               placeholder="Where is this equipment stored/located...">
                        <small class="form-text">Current storage location or work area</small>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"
                                  placeholder="Enter additional details, specifications, or notes about this equipment..."></textarea>
                        <small class="form-text">Additional details about the equipment</small>
                    </div>

                    <div class="form-group">
                        <label for="maintenance_notes" class="form-label">Maintenance Notes</label>
                        <textarea id="maintenance_notes" name="maintenance_notes" class="form-control" rows="3"
                                  placeholder="Any maintenance requirements, schedules, or special instructions..."></textarea>
                        <small class="form-text">Maintenance requirements and schedules</small>
                    </div>

                    <div class="form-group">
                        <label for="equipment_image" class="form-label">Equipment Image</label>
                        <input type="file" id="equipment_image" name="equipment_image" class="form-control" 
                               accept="image/*">
                        <small class="form-text">Upload a photo of the equipment (optional)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">💾</span>
                            Add Equipment
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="btn btn-secondary">
                            <span class="icon">❌</span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>