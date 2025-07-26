<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/MaterialController.php';

use MyApp\Controllers\MaterialController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$controller = new MaterialController($database);

$id = $_GET['id'] ?? null;
$success_message = '';
$error_message = '';

if ($id) {
    $material = $controller->getMaterialById($id);
    if (!$material) {
        header("Location: " . BASE_URL . "Views/materials/index.php?error=" . urlencode('Material not found'));
        exit;
    }
} else {
    header("Location: " . BASE_URL . "Views/materials/index.php?error=" . urlencode('No material ID provided'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $success = $controller->updateMaterialByName(
            $_POST['material_name'],
            $_POST['length'],
            $_POST['width'],
            $_POST['thickness'],
            $_POST['price'],
            $_POST['quantity_on_hand'],
            $_POST['type'],
            $_POST['vendor'],
            $_POST['item_no'],
            $_POST['item_url'],
            $_POST['image_url']
        );

        if ($success) {
            header("Location: " . BASE_URL . "Views/materials/index.php?success=" . urlencode('Material updated successfully'));
            exit;
        } else {
            $error_message = 'Failed to update material.';
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material - OMC</title>
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
                    <h1>Edit Material</h1>
                    <p>Update material information and specifications</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="nav-link">Materials</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Edit Material</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="btn btn-secondary">
                        <span class="icon">📦</span>
                        Back to Materials
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <div class="notification notification-error">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Material Information</h2>
                <p class="card-subtitle">Modify material details, dimensions, and specifications</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>Views/materials/edit_material.php?id=<?php echo $id; ?>" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="material_name" class="form-label required">Material Name</label>
                            <input type="text" id="material_name" name="material_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['material_name'] ?? ''); ?>" required
                                   placeholder="Enter material name...">
                            <small class="form-text">Descriptive name for the material</small>
                        </div>

                        <div class="form-group">
                            <label for="type" class="form-label">Material Type</label>
                            <input type="text" id="type" name="type" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['type'] ?? ''); ?>"
                                   placeholder="e.g., Wood, Metal, Plastic...">
                            <small class="form-text">Category or type of material</small>
                        </div>

                        <!-- Dimensions -->
                        <div class="form-group">
                            <label for="length" class="form-label">Length</label>
                            <input type="number" step="0.001" id="length" name="length" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['Length'] ?? ''); ?>"
                                   placeholder="Enter length...">
                            <small class="form-text">Length in inches</small>
                        </div>

                        <div class="form-group">
                            <label for="width" class="form-label">Width</label>
                            <input type="number" step="0.001" id="width" name="width" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['Width'] ?? ''); ?>"
                                   placeholder="Enter width...">
                            <small class="form-text">Width in inches</small>
                        </div>

                        <div class="form-group">
                            <label for="thickness" class="form-label">Thickness</label>
                            <input type="number" step="0.001" id="thickness" name="thickness" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['Thickness'] ?? ''); ?>"
                                   placeholder="Enter thickness...">
                            <small class="form-text">Thickness in inches</small>
                        </div>

                        <!-- Pricing & Inventory -->
                        <div class="form-group">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" step="0.01" id="price" name="price" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['Price'] ?? ''); ?>"
                                   placeholder="Enter price...">
                            <small class="form-text">Price per unit</small>
                        </div>

                        <div class="form-group">
                            <label for="quantity_on_hand" class="form-label">Quantity on Hand</label>
                            <input type="number" step="0.01" id="quantity_on_hand" name="quantity_on_hand" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['Quantity_on_Hand'] ?? ''); ?>"
                                   placeholder="Enter quantity...">
                            <small class="form-text">Current inventory quantity</small>
                        </div>

                        <!-- Vendor Information -->
                        <div class="form-group">
                            <label for="vendor" class="form-label">Vendor</label>
                            <input type="text" id="vendor" name="vendor" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['vendor_name'] ?? ''); ?>"
                                   placeholder="Enter vendor name...">
                            <small class="form-text">Supplier or vendor name</small>
                        </div>

                        <div class="form-group">
                            <label for="item_no" class="form-label">Item Number</label>
                            <input type="text" id="item_no" name="item_no" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['Item_no'] ?? ''); ?>"
                                   placeholder="Enter item number...">
                            <small class="form-text">Vendor item/part number</small>
                        </div>

                        <!-- URLs -->
                        <div class="form-group">
                            <label for="item_url" class="form-label">Item URL</label>
                            <input type="url" id="item_url" name="item_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['item_url'] ?? ''); ?>"
                                   placeholder="https://example.com/product...">
                            <small class="form-text">Link to vendor product page</small>
                        </div>

                        <div class="form-group">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" id="image_url" name="image_url" class="form-control" 
                                   value="<?php echo htmlspecialchars($material['image_url'] ?? ''); ?>"
                                   placeholder="https://example.com/image.jpg...">
                            <small class="form-text">Link to material image</small>
                        </div>
                    </div>

                    <div class="form-actions mt-6">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">💾</span>
                            Update Material
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="btn btn-secondary">
                            <span class="icon">❌</span>
                            Cancel
                        </a>
                        <button type="button" onclick="confirmDelete(<?php echo $id; ?>)" class="btn btn-danger">
                            <span class="icon">🗑️</span>
                            Delete Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function confirmDelete(materialId) {
            if (confirm('Are you sure you want to delete this material? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>public/materials/delete_material.php?id=' + materialId;
            }
        }
    </script>
</body>
</html>

