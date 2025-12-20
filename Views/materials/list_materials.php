<?php
require_once __DIR__ . '/../../config.php'; // Correct relative path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Material.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

// Instantiate the Database class with arguments
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Updated initialization
$materialsController = new MaterialController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_material_name'])) {
    $materialName = $_POST['delete_material_name'];
    error_log("Deleting material with name: $materialName"); // Log the material name being deleted
    $materialsController->deleteMaterialByName($materialName);
    header('Location: ' . BASE_URL . 'Views/materials/list_materials.php'); // Redirect to refresh the list after deletion
    exit;
}

$materials = $materialsController->getAllMaterials();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Materials - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <script>
        function openImage(url) {
            if (!url || url.trim() === '') {
                alert('No image URL available');
                return;
            }
            
            const imgWindow = window.open("", "_blank", "width=800,height=600");
            imgWindow.document.write(`
                <html>
                <head>
                    <title>Material Image</title>
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
                        .error-message {
                            color: white;
                            text-align: center;
                            font-family: Arial, sans-serif;
                        }
                    </style>
                </head>
                <body>
                    <button class="close-button" onclick="window.history.back()">Close</button>
                    <img src="${url}" alt="Material Image" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="error-message" style="display:none;">
                        <p>Image could not be loaded.</p>
                        <p><a href="${url}" target="_blank" style="color: #007bff;">Open image in new tab</a></p>
                    </div>
                </body>
                </html>
            `);
        }

        function handleImageError(img) {
            const link = document.createElement('a');
            link.href = img.src;
            link.target = '_blank';
            link.textContent = 'View Image';
            link.className = 'text-link';
            img.parentNode.replaceChild(link, img);
        }
    </script>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Material Management</h1>
                    <p>Manage inventory, track costs, and plan material requirements</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="nav-link">Materials Home</a>
            </nav>
        </div>
    </header>
    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Material Inventory</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="btn btn-primary">
                        <span class="icon">🪵</span>
                        Add Material
                    </a>
                </div>
            </div>
        </div>

        <!-- Materials Table Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">All Materials</h2>
                <p class="card-subtitle">Track your material inventory and vendor information</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Material</th>
                                <th>Dimensions</th>
                                <th>Pricing</th>
                                <th>Inventory</th>
                                <th>Vendor Info</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary"><?php echo htmlspecialchars($material['id'] ?? ''); ?></span>
                                    </td>
                                    <td>
                                        <div class="material-info">
                                            <strong><?php echo htmlspecialchars($material['material_name'] ?? ''); ?></strong>
                                            <?php if (!empty($material['type'])): ?>
                                                <div class="text-sm">
                                                    <span class="badge badge-info"><?php echo htmlspecialchars($material['type']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dimensions-info">
                                            <?php 
                                            $dimensions = array_filter([
                                                !empty($material['Length']) ? 'L: ' . $material['Length'] : '',
                                                !empty($material['Width']) ? 'W: ' . $material['Width'] : '',
                                                !empty($material['Thickness']) ? 'T: ' . $material['Thickness'] : ''
                                            ]);
                                            if (!empty($dimensions)): ?>
                                                <?php foreach ($dimensions as $dimension): ?>
                                                    <div class="text-sm"><?php echo htmlspecialchars($dimension); ?></div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No dimensions</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($material['Price'])): ?>
                                            <span class="badge badge-success">$<?php echo htmlspecialchars($material['Price']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">No price</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($material['Quantity_on_Hand'])): ?>
                                            <span class="badge badge-warning"><?php echo htmlspecialchars($material['Quantity_on_Hand']); ?> in stock</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Out of stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="vendor-info">
                                            <?php if (!empty($material['vendor_name'])): ?>
                                                <div class="text-sm"><strong><?php echo htmlspecialchars($material['vendor_name']); ?></strong></div>
                                            <?php endif; ?>
                                            <?php if (!empty($material['Item_no'])): ?>
                                                <div class="text-sm">Item: <?php echo htmlspecialchars($material['Item_no']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($material['item_url'])): ?>
                                                <div class="text-sm">
                                                    <a href="<?php echo htmlspecialchars($material['item_url']); ?>" target="_blank" class="text-link">
                                                        View Product
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($material['image_url'])): ?>
                                                <div class="text-sm mt-1">
                                                    <button onclick="openImage('<?php echo htmlspecialchars($material['image_url']); ?>')" 
                                                            class="btn btn-ghost btn-sm"
                                                            title="View material image">
                                                        🖼️ View Image
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo BASE_URL; ?>Views/materials/edit_material.php?id=<?php echo urlencode($material['id'] ?? ''); ?>" 
                                               class="btn btn-ghost btn-sm" title="Edit Material">
                                                <span class="icon">✏️</span>
                                                Edit
                                            </a>
                                            <form action="list_materials.php" method="post" 
                                                  onsubmit="return confirm('Are you sure you want to delete this material?');" 
                                                  style="display:inline;">
                                                <input type="hidden" name="delete_material_name" value="<?php echo htmlspecialchars($material['material_name'] ?? ''); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Material">
                                                    <span class="icon">🗑️</span>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>