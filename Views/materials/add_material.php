<?php
// Load config first
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once realpath(dirname(__FILE__) . '/../../config.php');

// Check for error or success messages
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
$success_message = isset($_GET['success']) ? 'Material added successfully!' : '';

// Debug output
echo "<!-- Debug: Config loaded, BASE_URL = " . BASE_URL . " -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
    <style>
        /* Fallback styles in case CSS doesn't load */
        body { font-family: Inter, Arial, sans-serif; margin: 0; padding: 20px; background: #f9fafb; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        button { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }
        button:hover { background: #1d4ed8; }
        .alert { padding: 16px; margin-bottom: 20px; border-radius: 6px; }
        .alert-error { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-success { background: #f0fdf4; color: #166534; border-left: 4px solid #10b981; }
    </style>
</head>
<body>
    
    <!-- Modern Header -->
    <header style="background: #2563eb; color: white; padding: 20px 0; margin-bottom: 30px;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h1 style="margin: 0; font-size: 28px;">Add New Material</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Add materials to your inventory with detailed specifications</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <h1>Add New Material</h1>
        
        <!-- Error/Success Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Add Material Form -->
        <form action="<?php echo BASE_URL; ?>public/materials/add_material.php" method="post">
            <div class="form-group">
                <label for="material_name">Material Name *</label>
                <input type="text" id="material_name" name="material_name" required placeholder="e.g., Oak Plywood 3/4&quot;">
            </div>
            
            <div class="form-group">
                <label for="type">Material Type</label>
                <input type="text" id="type" name="type" placeholder="e.g., Plywood, Hardwood, MDF">
            </div>
            
            <div class="form-group">
                <label for="length">Length (inches)</label>
                <input type="number" step="0.01" id="length" name="length" placeholder="48.00">
            </div>
            
            <div class="form-group">
                <label for="width">Width (inches)</label>
                <input type="number" step="0.01" id="width" name="width" placeholder="24.00">
            </div>
            
            <div class="form-group">
                <label for="thickness">Thickness (inches)</label>
                <input type="number" step="0.01" id="thickness" name="thickness" placeholder="0.75">
            </div>
            
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" step="0.01" id="price" name="price" placeholder="25.99">
            </div>
            
            <div class="form-group">
                <label for="quantity_on_hand">Quantity on Hand</label>
                <input type="number" id="quantity_on_hand" name="quantity_on_hand" placeholder="10">
            </div>
            
            <div class="form-group">
                <label for="vendor">Vendor/Supplier</label>
                <input type="text" id="vendor" name="vendor" placeholder="e.g., Home Depot, Lowes, Local Supplier">
            </div>
            
            <div class="form-group">
                <label for="item_no">Item/SKU Number</label>
                <input type="text" id="item_no" name="item_no" placeholder="SKU123456">
            </div>
            
            <div class="form-group">
                <label for="item_url">Product URL</label>
                <input type="url" id="item_url" name="item_url" placeholder="https://www.vendor.com/product">
            </div>
            
            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="url" id="image_url" name="image_url" placeholder="https://www.example.com/image.jpg">
            </div>

            <div style="margin-top: 30px;">
                <button type="submit">Add Material</button>
                <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" style="margin-left: 15px; text-decoration: none; color: #6b7280;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>