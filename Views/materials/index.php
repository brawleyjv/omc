<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Management - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <?php require_once BASE_PATH . '/Views/header.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Material Management</h1>
                <p class="text-muted">Manage your materials, inventory, and supplies</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add Material Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">➕</span>
                    </div>
                    <h3 class="card-title">Add New Material</h3>
                    <p class="text-muted mb-4">Add new materials to your inventory</p>
                    <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="btn btn-primary w-full">
                        <span class="icon">📦</span>
                        Add Material
                    </a>
                </div>
            </div>

            <!-- List Materials Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📋</span>
                    </div>
                    <h3 class="card-title">View All Materials</h3>
                    <p class="text-muted mb-4">Browse and manage all materials in your inventory</p>
                    <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="btn btn-primary w-full">
                        <span class="icon">👁️</span>
                        View Materials
                    </a>
                </div>
            </div>

            <!-- Search Materials Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🔍</span>
                    </div>
                    <h3 class="card-title">Search Materials</h3>
                    <p class="text-muted mb-4">Find specific materials by name, type, or vendor</p>
                    <a href="<?php echo BASE_URL; ?>Views/materials/search_materials.php" class="btn btn-primary w-full">
                        <span class="icon">🔍</span>
                        Search Materials
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-6">
            <div class="card-header">
                <h2>Material Overview</h2>
                <p class="text-muted">Quick statistics about your material inventory</p>
            </div>
            <div class="card-body">
                <?php
                try {
                    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $conn = $database->getConnection();
                    
                    // Get material count
                    $stmt = $conn->query("SELECT COUNT(*) as material_count FROM materials");
                    $material_count = $stmt->fetch(PDO::FETCH_ASSOC)['material_count'];
                    
                    // Get low stock materials
                    $stmt = $conn->query("SELECT material_name, Quantity_on_Hand FROM materials WHERE Quantity_on_Hand < 5 ORDER BY Quantity_on_Hand ASC LIMIT 5");
                    $low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Get total inventory value
                    $stmt = $conn->query("SELECT SUM(Price * Quantity_on_Hand) as total_value FROM materials");
                    $total_value = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'] ?? 0;
                    
                } catch (Exception $e) {
                    $material_count = 0;
                    $low_stock = [];
                    $total_value = 0;
                }
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $material_count; ?></div>
                            <div class="stats-label">Total Materials</div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="stats-card">
                            <div class="stats-number">$<?php echo number_format($total_value, 2); ?></div>
                            <div class="stats-label">Inventory Value</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Low Stock Alert</h4>
                        <?php if (!empty($low_stock)): ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($low_stock, 0, 3) as $material): ?>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm"><?php echo htmlspecialchars($material['material_name']); ?></span>
                                        <span class="badge badge-warning"><?php echo $material['Quantity_on_Hand']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">All materials well stocked.</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="text-primary text-sm">View all materials →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>