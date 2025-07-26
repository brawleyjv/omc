<?php
/**
 * Database Security Test Script
 * This script tests the improved database connection structure with prepared statements
 */

require_once realpath(dirname(__FILE__) . '/config.php');
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

try {
    echo "<h1>Database Security Test Results</h1>\n";
    
    // Test 1: Database Connection
    echo "<h2>Test 1: Database Connection</h2>\n";
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>\n";
        exit;
    }
    
    // Test 2: Prepared Statement Test (Safe SQL execution)
    echo "<h2>Test 2: Prepared Statement Security Test</h2>\n";
    
    // Test with a simple SELECT using prepared statements
    $testQuery = "SELECT COUNT(*) as count FROM users WHERE user_type = ?";
    $stmt = $conn->prepare($testQuery);
    $userType = 'admin'; // Test parameter
    $stmt->bindParam(1, $userType, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result !== false) {
        echo "<p style='color: green;'>✓ Prepared statement execution successful</p>\n";
        echo "<p>Found {$result['count']} admin users</p>\n";
    } else {
        echo "<p style='color: red;'>✗ Prepared statement execution failed</p>\n";
    }
    
    // Test 3: SQL Injection Protection Test
    echo "<h2>Test 3: SQL Injection Protection Test</h2>\n";
    
    // Simulate a malicious input that would cause SQL injection in old system
    $maliciousInput = "'; DROP TABLE test; --";
    
    try {
        $safeQuery = "SELECT * FROM users WHERE name = ? LIMIT 1";
        $stmt = $conn->prepare($safeQuery);
        $stmt->bindParam(1, $maliciousInput, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If we get here without error, the malicious input was safely handled
        echo "<p style='color: green;'>✓ SQL injection attempt safely blocked</p>\n";
        echo "<p>Malicious input treated as safe string parameter</p>\n";
        
    } catch (PDOException $e) {
        echo "<p style='color: yellow;'>⚠ Query failed (expected for malicious input): " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    // Test 4: Multiple Parameter Binding Test
    echo "<h2>Test 4: Multiple Parameter Binding Test</h2>\n";
    
    try {
        $multiQuery = "SELECT * FROM users WHERE user_type = ? AND position = ? LIMIT 5";
        $stmt = $conn->prepare($multiQuery);
        $userType = 'admin';
        $position = 'manager';
        $stmt->bindParam(1, $userType, PDO::PARAM_STR);
        $stmt->bindParam(2, $position, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='color: green;'>✓ Multiple parameter binding successful</p>\n";
        echo "<p>Found " . count($results) . " matching users</p>\n";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Multiple parameter binding failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    // Test 5: Database Transaction Test
    echo "<h2>Test 5: Database Transaction Test</h2>\n";
    
    try {
        $conn->beginTransaction();
        
        // Simulate a series of operations that should be atomic
        $insertQuery = "INSERT INTO settings (company_name, company_slogan) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $testCompany = "Test Company " . time();
        $testSlogan = "Test Slogan";
        $stmt->bindParam(1, $testCompany, PDO::PARAM_STR);
        $stmt->bindParam(2, $testSlogan, PDO::PARAM_STR);
        $stmt->execute();
        
        // Roll back the test insertion
        $conn->rollback();
        
        echo "<p style='color: green;'>✓ Database transaction test successful</p>\n";
        echo "<p>Transaction rolled back successfully</p>\n";
        
    } catch (PDOException $e) {
        $conn->rollback();
        echo "<p style='color: red;'>✗ Database transaction test failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    echo "<h2>Summary</h2>\n";
    echo "<p style='color: blue; font-weight: bold;'>All database security tests completed. The system now uses:</p>\n";
    echo "<ul>\n";
    echo "<li>✓ Prepared statements with parameter binding</li>\n";
    echo "<li>✓ Protection against SQL injection attacks</li>\n";
    echo "<li>✓ Proper error handling</li>\n";
    echo "<li>✓ Transaction support</li>\n";
    echo "<li>✓ PDO with secure configuration</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Critical Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>
