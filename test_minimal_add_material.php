<?php
// Minimal test for add_material.php functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing add_material.php</h1>";

echo "<p><strong>Step 1:</strong> Loading config.php...</p>";
try {
    require_once 'config.php';
    echo "<p>✅ Config loaded</p>";
    echo "<p>BASE_PATH: " . BASE_PATH . "</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Config failed: " . $e->getMessage() . "</p>";
    exit;
}

echo "<p><strong>Step 2:</strong> Test minimal HTML output...</p>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Material Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { padding: 8px; width: 300px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Add New Material</h1>
    
    <form action="<?php echo BASE_URL; ?>public/materials/add_material.php" method="post">
        <div class="form-group">
            <label>Material Name:</label>
            <input type="text" name="material_name" required>
        </div>
        
        <div class="form-group">
            <label>Length:</label>
            <input type="number" step="0.01" name="length">
        </div>
        
        <div class="form-group">
            <label>Width:</label>
            <input type="number" step="0.01" name="width">
        </div>
        
        <div class="form-group">
            <label>Price:</label>
            <input type="number" step="0.01" name="price">
        </div>
        
        <button type="submit">Add Material</button>
    </form>
    
    <p><a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php">Back to Materials List</a></p>
</body>
</html>
