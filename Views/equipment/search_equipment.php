<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . 'Controllers/EquipmentController.php';

// Get search term and results
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$equipment_result = [];

if (!empty($search_term)) {
    try {
        $equipmentController = new EquipmentController();
        $equipment_result = $equipmentController->searchEquipment($search_term);
    } catch (Exception $e) {
        error_log("Equipment search error: " . $e->getMessage());
        $equipment_result = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Equipment - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Search Equipment</h1>
                    <p>Find equipment by name, type, manufacturer, or location</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/index.php" class="nav-link">Equipment Menu</a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="nav-link">All Equipment</a>
                <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="nav-link">Add Equipment</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Equipment Search</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="btn btn-primary">
                        <span class="icon">➕</span>
                        Add New Equipment
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Equipment Search</h2>
                <p class="card-subtitle">Search for equipment by name, type, manufacturer, model, serial number, or location</p>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_term" class="form-label">Search Query</label>
                            <input type="text" id="search_term" name="search_term" class="form-control" 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   placeholder="Enter equipment name, type, manufacturer, model, serial number, or location...">
                            <small class="form-text">Search across equipment names, types, manufacturers, models, serial numbers, and locations</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🔍</span>
                            Search Equipment
                        </button>
                        <?php if (!empty($search_term)): ?>
                            <a href="<?php echo BASE_URL; ?>Views/equipment/search_equipment.php" class="btn btn-secondary">
                                <span class="icon">🔄</span>
                                Clear Search
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if (!empty($search_term)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Search Results</h2>
                    <p class="card-subtitle">
                        <?php if (empty($equipment_result)): ?>
                            No equipment found matching "<?php echo htmlspecialchars($search_term); ?>"
                        <?php else: ?>
                            Found <?php echo count($equipment_result); ?> equipment item(s) matching "<?php echo htmlspecialchars($search_term); ?>"
                        <?php endif; ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if (empty($equipment_result)): ?>
                        <div class="notification notification-info">
                            <p><strong>No equipment found.</strong></p>
                            <p>Try adjusting your search terms or browse all equipment.</p>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>Views/equipment/list_equipment.php" class="btn btn-primary">
                                    <span class="icon">📋</span>
                                    View All Equipment
                                </a>
                                <a href="<?php echo BASE_URL; ?>Views/equipment/add_equipment.php" class="btn btn-ghost">
                                    <span class="icon">➕</span>
                                    Add New Equipment
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($equipment_result as $equipment): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                            <!-- Equipment Info -->
                                            <div class="lg:col-span-3">
                                                <div class="flex items-start justify-between mb-4">
                                                    <div>
                                                        <h3 class="card-title"><?php echo htmlspecialchars($equipment['equipment_name']); ?></h3>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="badge badge-<?php echo $equipment['status'] === 'operational' ? 'success' : ($equipment['status'] === 'maintenance' ? 'warning' : 'danger'); ?>">
                                                                <?php echo ucfirst($equipment['status']); ?>
                                                            </span>
                                                            <span class="text-muted"><?php echo htmlspecialchars($equipment['equipment_type']); ?></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <?php if (!empty($equipment['manufacturer'])): ?>
                                                        <div class="info-item">
                                                            <label class="info-label">Manufacturer:</label>
                                                            <span class="info-value"><?php echo htmlspecialchars($equipment['manufacturer']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($equipment['model_number'])): ?>
                                                        <div class="info-item">
                                                            <label class="info-label">Model:</label>
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
                                                    <?php if (!empty($equipment['purchase_date'])): ?>
                                                        <div class="info-item">
                                                            <label class="info-label">Purchase Date:</label>
                                                            <span class="info-value"><?php echo htmlspecialchars($equipment['purchase_date']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($equipment['current_value'])): ?>
                                                        <div class="info-item">
                                                            <label class="info-label">Current Value:</label>
                                                            <span class="info-value">$<?php echo number_format($equipment['current_value'], 2); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if (!empty($equipment['notes'])): ?>
                                                    <div class="mt-4">
                                                        <label class="info-label">Notes:</label>
                                                        <p class="text-muted"><?php echo htmlspecialchars($equipment['notes']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Equipment Actions -->
                                                <div class="btn-group mt-4">
                                                    <a href="<?php echo BASE_URL; ?>Views/equipment/edit_equipment.php?id=<?php echo $equipment['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <span class="icon">✏️</span>
                                                        Edit Equipment
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>Views/equipment/view_equipment.php?id=<?php echo $equipment['id']; ?>" 
                                                       class="btn btn-secondary btn-sm">
                                                        <span class="icon">👁️</span>
                                                        View Details
                                                    </a>
                                                    <button onclick="confirmDelete(<?php echo $equipment['id']; ?>)" 
                                                            class="btn btn-danger btn-sm">
                                                        <span class="icon">🗑️</span>
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Equipment Image -->
                                            <div class="text-center">
                                                <?php if (!empty($equipment['image_path'])): ?>
                                                    <?php $image_url = BASE_URL . $equipment['image_path']; ?>
                                                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                                         alt="Equipment Image" 
                                                         class="image-thumbnail cursor-pointer"
                                                         onclick="openImage('<?php echo htmlspecialchars($image_url); ?>')">
                                                <?php else: ?>
                                                    <div class="no-image-placeholder">
                                                        <span class="icon">🔧</span>
                                                        <p>No image available</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

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

        // Auto-focus the search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_term');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>
