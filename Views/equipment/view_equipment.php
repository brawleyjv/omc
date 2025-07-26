<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/EquipmentController.php';

// Get equipment ID from URL
$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$equipment_id) {
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('Equipment ID is required.'));
    exit();
}

try {
    $equipmentController = new EquipmentController();
    $equipment = $equipmentController->getEquipmentById($equipment_id);
    
    if (!$equipment) {
        header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('Equipment not found.'));
        exit();
    }
} catch (Exception $e) {
    error_log("Equipment view error: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'Views/equipment/list_equipment.php?error=' . urlencode('An error occurred while loading equipment.'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($equipment['equipment_name']); ?> - Equipment Details - OMC</title>
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
                <h1><?php echo htmlspecialchars($equipment['equipment_name']); ?></h1>
                <p class="text-muted">Equipment Details and Information</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="btn btn-secondary">
                    <span class="icon">📋</span>
                    Back to List
                </a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/edit_equipment.php?id=<?php echo $equipment['id']; ?>" class="btn btn-primary">
                    <span class="icon">✏️</span>
                    Edit Equipment
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h2>Equipment Information</h2>
                        <span class="badge badge-<?php echo $equipment['status'] === 'operational' ? 'success' : ($equipment['status'] === 'maintenance' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($equipment['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="info-item">
                                <label class="info-label">Equipment Name:</label>
                                <span class="info-value"><?php echo htmlspecialchars($equipment['equipment_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Equipment Type:</label>
                                <span class="info-value"><?php echo htmlspecialchars($equipment['equipment_type']); ?></span>
                            </div>
                            <?php if (!empty($equipment['manufacturer'])): ?>
                                <div class="info-item">
                                    <label class="info-label">Manufacturer:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($equipment['manufacturer']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($equipment['model_number'])): ?>
                                <div class="info-item">
                                    <label class="info-label">Model Number:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($equipment['model_number']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($equipment['serial_number'])): ?>
                                <div class="info-item">
                                    <label class="info-label">Serial Number:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($equipment['serial_number']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($equipment['location'])): ?>
                                <div class="info-item">
                                    <label class="info-label">Location:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($equipment['location']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <?php if (!empty($equipment['purchase_date']) || !empty($equipment['purchase_price']) || !empty($equipment['current_value'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Financial Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php if (!empty($equipment['purchase_date'])): ?>
                                    <div class="info-item">
                                        <label class="info-label">Purchase Date:</label>
                                        <span class="info-value"><?php echo date('M j, Y', strtotime($equipment['purchase_date'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($equipment['purchase_price'])): ?>
                                    <div class="info-item">
                                        <label class="info-label">Purchase Price:</label>
                                        <span class="info-value">$<?php echo number_format($equipment['purchase_price'], 2); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($equipment['current_value'])): ?>
                                    <div class="info-item">
                                        <label class="info-label">Current Value:</label>
                                        <span class="info-value">$<?php echo number_format($equipment['current_value'], 2); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Maintenance Information -->
                <div class="card">
                    <div class="card-header">
                        <h2>Maintenance Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="info-item">
                                <label class="info-label">Operating Hours:</label>
                                <span class="info-value"><?php echo number_format($equipment['operating_hours'], 2); ?> hours</span>
                            </div>
                            <div class="info-item">
                                <label class="info-label">Maintenance Interval:</label>
                                <span class="info-value"><?php echo $equipment['maintenance_interval_days']; ?> days</span>
                            </div>
                            <?php if (!empty($equipment['last_maintenance_date'])): ?>
                                <div class="info-item">
                                    <label class="info-label">Last Maintenance:</label>
                                    <span class="info-value"><?php echo date('M j, Y', strtotime($equipment['last_maintenance_date'])); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($equipment['next_maintenance_date'])): ?>
                                <div class="info-item">
                                    <label class="info-label">Next Maintenance:</label>
                                    <span class="info-value"><?php echo date('M j, Y', strtotime($equipment['next_maintenance_date'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Technical Specifications -->
                <?php if (!empty($equipment['power_consumption']) || !empty($equipment['dimensions']) || !empty($equipment['weight_kg'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Technical Specifications</h2>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php if (!empty($equipment['power_consumption'])): ?>
                                    <div class="info-item">
                                        <label class="info-label">Power Consumption:</label>
                                        <span class="info-value"><?php echo htmlspecialchars($equipment['power_consumption']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($equipment['dimensions'])): ?>
                                    <div class="info-item">
                                        <label class="info-label">Dimensions:</label>
                                        <span class="info-value"><?php echo htmlspecialchars($equipment['dimensions']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($equipment['weight_kg'])): ?>
                                    <div class="info-item">
                                        <label class="info-label">Weight:</label>
                                        <span class="info-value"><?php echo number_format($equipment['weight_kg'], 2); ?> kg</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Notes -->
                <?php if (!empty($equipment['notes'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Notes</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($equipment['notes'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Equipment Image -->
                <div class="card">
                    <div class="card-header">
                        <h3>Equipment Image</h3>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($equipment['image_path'])): ?>
                            <?php $image_url = BASE_URL . $equipment['image_path']; ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                 alt="Equipment Image" 
                                 class="w-full rounded-lg cursor-pointer"
                                 onclick="openImage('<?php echo htmlspecialchars($image_url); ?>')">
                        <?php else: ?>
                            <div class="no-image-placeholder py-12">
                                <span class="icon text-6xl">🔧</span>
                                <p class="mt-2 text-muted">No image available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body space-y-3">
                        <a href="<?php echo BASE_URL; ?>Views/equipment/edit_equipment.php?id=<?php echo $equipment['id']; ?>" 
                           class="btn btn-primary w-full">
                            <span class="icon">✏️</span>
                            Edit Equipment
                        </a>
                        <button onclick="confirmDelete(<?php echo $equipment['id']; ?>)" 
                                class="btn btn-danger w-full">
                            <span class="icon">🗑️</span>
                            Delete Equipment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(equipmentId) {
            if (confirm('Are you sure you want to delete this equipment? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/equipment/delete_equipment.php?id=' + equipmentId;
            }
        }

        function openImage(url) {
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Equipment Image</title>
                    <style>
                        body { margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #000; }
                        img { max-width: 100%; max-height: 100%; }
                        .close-button {
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            background-color: #DC3545;
                            color: white;
                            border: none;
                            padding: 10px;
                            cursor: pointer;
                            font-size: 16px;
                            border-radius: 5px;
                        }
                        .close-button:hover {
                            background-color: #c82333;
                        }
                    </style>
                </head>
                <body>
                    <button class="close-button" onclick="window.close()">Close</button>
                    <img src="${url}" alt="Equipment Image">
                </body>
                </html>
            `);
        }
    </script>
</body>
</html>
