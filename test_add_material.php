<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing add_material.php functionality</h2>";

try {
    echo "<p>1. Testing config.php inclusion...</p>";
    require_once realpath(dirname(__FILE__) . '/config.php');
    echo "<p>✓ Config loaded successfully</p>";
    echo "<p>BASE_PATH: " . BASE_PATH . "</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";

    echo "<p>2. Testing Database class...</p>";
    require_once BASE_PATH . '/Models/Database.php';
    echo "<p>✓ Database class loaded</p>";

    echo "<p>3. Testing Material class...</p>";
    require_once BASE_PATH . '/Models/Material.php';
    echo "<p>✓ Material class loaded</p>";

    echo "<p>4. Testing MaterialController class...</p>";
    require_once BASE_PATH . '/Controllers/MaterialController.php';
    echo "<p>✓ MaterialController class loaded</p>";

    echo "<p>5. Testing database connection...</p>";
    use MyApp\Models\Database;
    use MyApp\Controllers\MaterialController;

    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    echo "<p>✓ Database object created</p>";

    $connection = $database->getConnection();
    if ($connection) {
        echo "<p>✓ Database connection successful</p>";
    } else {
        echo "<p>❌ Database connection failed</p>";
    }

    echo "<p>6. Testing MaterialController instantiation...</p>";
    $materialsController = new MaterialController($database);
    echo "<p>✓ MaterialController created successfully</p>";

    echo "<p>7. Testing form submission simulation...</p>";
    $result = $materialsController->submitMaterial(
        'Test Material',
        12.0,
        8.0,
        0.75,
        25.99,
        5,
        'Plywood',
        'Test Vendor',
        'TEST123',
        'http://example.com',
        'http://example.com/image.jpg'
    );
    echo "<p>✓ Material submission test successful</p>";

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
