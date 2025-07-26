<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/EquipmentController.php';

try {
    $equipmentController = new EquipmentController();
    $equipment = $equipmentController->listEquipment();
} catch (Exception $e) {
    $equipment = [];
    error_log("Equipment query error: " . $e->getMessage());
}

// Get success message from URL parameters
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment List - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Equipment Inventory</h1>
                <p class="text-muted">Manage all your tools, machinery, and equipment</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/equipment/index.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Equipment Menu
                </a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="btn btn-primary">
                    <span class="icon">➕</span>
                    Add Equipment
                </a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (!empty($success_message)): ?>
            <div class="notification notification-success">
                <p><strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($equipment)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Equipment List</h2>
                    <p class="text-muted">Total: <?php echo count($equipment); ?> items</p>
                </div>
                <div class="table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Equipment Name</th>
                                <th>Type</th>
                                <th>Manufacturer</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipment as $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-semibold"><?php echo htmlspecialchars($item['equipment_name']); ?></div>
                                        <?php if (!empty($item['model_number'])): ?>
                                            <div class="text-sm text-muted">Model: <?php echo htmlspecialchars($item['model_number']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo htmlspecialchars($item['equipment_type']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item['manufacturer'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $item['status'];
                                        $badge_class = $status === 'operational' ? 'badge-success' : 
                                                      ($status === 'maintenance' ? 'badge-warning' : 
                                                      ($status === 'repair' ? 'badge-danger' : 'badge-secondary'));
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>Views/equipment/edit_equipment.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-secondary">
                                                <span class="icon">✏️</span>
                                                Edit
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>Views/equipment/view_equipment.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <span class="icon">👁️</span>
                                                View
                                            </a>
                                            <button onclick="confirmDelete(<?php echo $item['id']; ?>)" 
                                                    class="btn btn-sm btn-danger">
                                                <span class="icon">🗑️</span>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <div class="empty-state">
                        <span class="icon text-6xl mb-4">🔧</span>
                        <h3>No Equipment Found</h3>
                        <p class="text-muted">You haven't added any equipment yet. Start building your equipment inventory.</p>
                        <div class="mt-4">
                            <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="btn btn-primary">
                                <span class="icon">➕</span>
                                Add First Equipment
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(equipmentId) {
            if (confirm('Are you sure you want to delete this equipment? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/equipment/delete_equipment.php?id=' + equipmentId;
            }
        }
    </script>
</body>
</html>
