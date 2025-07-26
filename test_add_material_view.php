<?php
// Test the add_material.php view file directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Views/materials/add_material.php</h2>";

echo "<p>1. Testing config.php path...</p>";
$config_path = realpath(dirname(__FILE__) . '/Views/materials/../../config.php');
echo "<p>Config path: " . $config_path . "</p>";
echo "<p>Config exists: " . (file_exists($config_path) ? 'YES' : 'NO') . "</p>";

try {
    require_once realpath(dirname(__FILE__) . '/config.php');
    echo "<p>✓ Config loaded successfully</p>";
    echo "<p>BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'NOT DEFINED') . "</p>";
    echo "<p>BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Config error: " . $e->getMessage() . "</p>";
}

echo "<p>2. Testing direct include of add_material.php...</p>";
try {
    ob_start();
    include 'Views/materials/add_material.php';
    $output = ob_get_clean();
    
    if (empty($output)) {
        echo "<p>❌ No output from add_material.php - this is the problem!</p>";
    } else {
        echo "<p>✓ Got output from add_material.php (" . strlen($output) . " characters)</p>";
        echo "<details><summary>Output Preview</summary><pre>" . htmlspecialchars(substr($output, 0, 500)) . "...</pre></details>";
    }
} catch (Exception $e) {
    echo "<p>❌ Include error: " . $e->getMessage() . "</p>";
}
?>
