<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Material.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Models\Database;
use MyApp\Controllers\MaterialController;

// Instantiate the Database class with required arguments
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$materialsController = new MaterialController($database);

$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';
$materials = [];

if (!empty($search_term)) {
    // Use MaterialController to search materials
    $materials = $materialsController->searchMaterial($search_term);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Materials - OMC</title>
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
                    <h1>Search Materials</h1>
                    <p>Find materials by name, type, vendor, or item number</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="nav-link">All Materials</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="nav-link">Add Material</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Material Search</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="btn btn-primary">
                        <span class="icon">📦</span>
                        Add New Material
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Material Search</h2>
                <p class="card-subtitle">Search for materials by name, type, vendor, or item number</p>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_term" class="form-label">Search Query</label>
                            <input type="text" id="search_term" name="search_term" class="form-control" 
                                   value="<?php echo htmlspecialchars($search_term); ?>"
                                   placeholder="Enter material name, type, vendor, or item number...">
                            <small class="form-text">Search across material names, types, vendors, and item numbers</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🔍</span>
                            Search Materials
                        </button>
                        <?php if (!empty($search_term)): ?>
                            <a href="<?php echo BASE_URL; ?>Views/materials/search_materials.php" class="btn btn-secondary">
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
                        <?php if (empty($materials)): ?>
                            No materials found matching "<?php echo htmlspecialchars($search_term); ?>"
                        <?php else: ?>
                            Found <?php echo count($materials); ?> material(s) matching "<?php echo htmlspecialchars($search_term); ?>"
                        <?php endif; ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php if (empty($materials)): ?>
                        <div class="notification notification-info">
                            <p><strong>No materials found.</strong></p>
                            <p>Try adjusting your search terms or browse all materials.</p>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="btn btn-primary">
                                    <span class="icon">📦</span>
                                    View All Materials
                                </a>
                                <a href="<?php echo BASE_URL; ?>Views/materials/add_material.php" class="btn btn-ghost">
                                    <span class="icon">➕</span>
                                    Add New Material
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Material Name</th>
                                        <th>Dimensions</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Type</th>
                                        <th>Vendor</th>
                                        <th>Item Info</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materials as $material): ?>
                                        <tr>
                                            <td>
                                                <div class="font-semibold"><?php echo htmlspecialchars($material['material_name']); ?></div>
                                            </td>
                                            <td>
                                                <div class="text-sm">
                                                    <strong>L:</strong> <?php echo htmlspecialchars($material['Length']); ?><br>
                                                    <strong>W:</strong> <?php echo htmlspecialchars($material['Width']); ?><br>
                                                    <strong>T:</strong> <?php echo htmlspecialchars($material['Thickness']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">$<?php echo htmlspecialchars($material['Price']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $material['Quantity_on_Hand'] > 0 ? 'badge-info' : 'badge-warning'; ?>">
                                                    <?php echo htmlspecialchars($material['Quantity_on_Hand']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($material['type']); ?></span>
                                            </td>
                                            <td>
                                                <div class="text-sm">
                                                    <?php echo htmlspecialchars($material['vendor_name'] ?? 'N/A'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-sm">
                                                    <strong>Item #:</strong> <?php echo htmlspecialchars($material['Item_no']); ?>
                                                    <?php if (!empty($material['item_url'])): ?>
                                                        <br><a href="<?php echo htmlspecialchars($material['item_url']); ?>" target="_blank" class="text-primary">
                                                            <span class="icon">🔗</span> View Item
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($material['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($material['image_url']); ?>" 
                                                         alt="Material Image" 
                                                         class="image-thumbnail cursor-pointer" 
                                                         onclick="openImage('<?php echo htmlspecialchars($material['image_url']); ?>')"
                                                         onerror="this.style.display='none'; this.parentNode.innerHTML='<span class=\'text-muted\'>No image</span>';">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo BASE_URL; ?>Views/materials/edit_material.php?id=<?php echo $material['id']; ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <span class="icon">✏️</span>
                                                        Edit
                                                    </a>
                                                    <button onclick="confirmDelete(<?php echo $material['id']; ?>)" 
                                                            class="btn btn-danger btn-sm">
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
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function confirmDelete(materialId) {
            if (confirm('Are you sure you want to delete this material? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/materials/delete_material.php?id=' + materialId;
            }
        }

        function openImage(url) {
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
                    </style>
                </head>
                <body>
                    <button class="close-button" onclick="window.close()">Close</button>
                    <img src="${url}" alt="Material Image" onerror="this.style.display='none'; document.body.innerHTML='<p style=color:white;text-align:center;>Image could not be loaded</p>';">
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