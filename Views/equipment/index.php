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
    <title>Equipment Management - OMC</title>
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
                <h1>Equipment Management</h1>
                <p class="text-muted">Manage your tools, machinery, and equipment</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add Equipment Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">➕</span>
                    </div>
                    <h3 class="card-title">Add New Equipment</h3>
                    <p class="text-muted mb-4">Add new tools, machinery, or equipment to your inventory</p>
                    <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="btn btn-primary w-full">
                        <span class="icon">🔧</span>
                        Add Equipment
                    </a>
                </div>
            </div>

            <!-- List Equipment Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📋</span>
                    </div>
                    <h3 class="card-title">View All Equipment</h3>
                    <p class="text-muted mb-4">Browse and manage all equipment in your system</p>
                    <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="btn btn-primary w-full">
                        <span class="icon">👁️</span>
                        View Equipment
                    </a>
                </div>
            </div>

            <!-- Search Equipment Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🔍</span>
                    </div>
                    <h3 class="card-title">Search Equipment</h3>
                    <p class="text-muted mb-4">Find specific equipment by name, type, or category</p>
                    <a href="<?php echo BASE_URL; ?>Views/equipment/search_equipment.php" class="btn btn-primary w-full">
                        <span class="icon">🔍</span>
                        Search Equipment
                    </a>
                </div>
            </div>

            <!-- Maintenance Schedule Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🛠️</span>
                    </div>
                    <h3 class="card-title">Maintenance Schedule</h3>
                    <p class="text-muted mb-4">Track equipment maintenance and service schedules</p>
                    <a href="<?php echo BASE_URL; ?>Views/equipment/maintenance.php" class="btn btn-primary w-full">
                        <span class="icon">⚙️</span>
                        Maintenance
                    </a>
                </div>
            </div>

            <!-- Equipment Reports Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📊</span>
                    </div>
                    <h3 class="card-title">Equipment Reports</h3>
                    <p class="text-muted mb-4">Generate reports on equipment usage and status</p>
                    <a href="<?php echo BASE_URL; ?>Views/equipment/reports.php" class="btn btn-primary w-full">
                        <span class="icon">📈</span>
                        Reports
                    </a>
                </div>
            </div>

            <!-- Equipment Categories Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🏷️</span>
                    </div>
                    <h3 class="card-title">Equipment Categories</h3>
                    <p class="text-muted mb-4">Manage equipment types and categories</p>
                    <a href="<?php echo BASE_URL; ?>Views/equipment/categories.php" class="btn btn-primary w-full">
                        <span class="icon">📂</span>
                        Categories
                    </a>
                </div>
            </div>
        </div>

        <!-- Equipment Overview -->
        <div class="card mt-6">
            <div class="card-header">
                <h2>Equipment Overview</h2>
                <p class="text-muted">Quick statistics about your equipment inventory</p>
            </div>
            <div class="card-body">
                <?php
                try {
                    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                    $conn = $database->getConnection();
                    
                    // Check if equipment table exists and get equipment count
                    $equipment_count = 0;
                    $recent_equipment = [];
                    $equipment_types = [];
                    
                    try {
                        $stmt = $conn->query("SELECT COUNT(*) as equipment_count FROM equipment");
                        $equipment_count = $stmt->fetch(PDO::FETCH_ASSOC)['equipment_count'];
                        
                        // Get recent equipment (if table has a date column)
                        $stmt = $conn->query("SELECT equipment_name, type, created_at FROM equipment ORDER BY id DESC LIMIT 5");
                        $recent_equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Get equipment types
                        $stmt = $conn->query("SELECT type, COUNT(*) as count FROM equipment GROUP BY type ORDER BY count DESC LIMIT 5");
                        $equipment_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                    } catch (PDOException $e) {
                        // Equipment table might not exist yet or might have different structure
                        $equipment_count = 0;
                        $recent_equipment = [];
                        $equipment_types = [];
                    }
                    
                } catch (Exception $e) {
                    $equipment_count = 0;
                    $recent_equipment = [];
                    $equipment_types = [];
                }
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $equipment_count; ?></div>
                            <div class="stats-label">Total Equipment</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Equipment Types</h4>
                        <?php if (!empty($equipment_types)): ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($equipment_types, 0, 3) as $type): ?>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm"><?php echo htmlspecialchars($type['type'] ?? 'Unknown'); ?></span>
                                        <span class="badge badge-info"><?php echo $type['count']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">No equipment types available yet.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Recent Equipment</h4>
                        <?php if (!empty($recent_equipment)): ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($recent_equipment, 0, 3) as $equipment): ?>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm"><?php echo htmlspecialchars($equipment['equipment_name'] ?? 'N/A'); ?></span>
                                        <span class="text-xs text-muted">
                                            <?php 
                                            if (!empty($equipment['created_at'])) {
                                                echo date('M j', strtotime($equipment['created_at']));
                                            } else {
                                                echo 'Recently';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">No equipment added yet.</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="text-primary text-sm">View all equipment →</a>
                        </div>
                    </div>
                </div>
                
                <?php if ($equipment_count == 0): ?>
                    <div class="notification notification-info mt-6">
                        <h4>Get Started with Equipment Management</h4>
                        <p>Your equipment inventory is empty. Start by adding your first piece of equipment to track tools, machinery, and other equipment in your workshop.</p>
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="btn btn-primary">
                                <span class="icon">🔧</span>
                                Add First Equipment
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
