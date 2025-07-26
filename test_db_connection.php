<?php
/**
 * Database Connection Test Script
 * Tests the database connection with current configuration
 */

require_once realpath(dirname(__FILE__) . '/config.php');
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

echo "<h1>Database Connection Test</h1>\n";

// Display current configuration (without exposing password)
echo "<h2>Current Configuration:</h2>\n";
echo "<p>Host: " . DB_HOST . "</p>\n";
echo "<p>User: " . DB_USER . "</p>\n";
echo "<p>Database: " . DB_NAME . "</p>\n";
echo "<p>Password: " . (DB_PASSWORD ? "***SET***" : "***NOT SET***") . "</p>\n";

try {
    echo "<h2>Testing Database Connection...</h2>\n";
    
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>\n";
        
        // Test a simple query
        echo "<h3>Testing Basic Query...</h3>\n";
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM vendors");
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "<p style='color: green;'>✓ Query executed successfully!</p>\n";
        echo "<p>Found {$result['count']} vendors in the database.</p>\n";
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<p><strong>If connection is successful, vendor list should now work!</strong></p>\n";
?>
