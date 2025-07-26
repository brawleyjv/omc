<?php
// Test script to verify all USER management pages are working correctly
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/Models/Database.php';

echo "<h1>OMC User Management System Test</h1>";

// Test 1: Check if BASE_PATH is defined
echo "<h2>Test 1: BASE_PATH Configuration</h2>";
if (defined('BASE_PATH')) {
    echo "<p style='color: green;'>✓ BASE_PATH is defined: " . BASE_PATH . "</p>";
} else {
    echo "<p style='color: red;'>✗ BASE_PATH is NOT defined</p>";
}

// Test 2: Check database constants
echo "<h2>Test 2: Database Configuration</h2>";
$db_constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
foreach ($db_constants as $constant) {
    if (defined($constant)) {
        echo "<p style='color: green;'>✓ $constant is defined</p>";
    } else {
        echo "<p style='color: red;'>✗ $constant is NOT defined</p>";
    }
}

// Test 3: Check database connection
echo "<h2>Test 3: Database Connection</h2>";
try {
    use MyApp\Models\Database;
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getConnection();
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>";
}

// Test 4: Check if user management files exist and are accessible
echo "<h2>Test 4: User Management Files</h2>";
$user_files = [
    'Views/Users/index.php',
    'Views/Users/list_users.php',
    'Views/Users/add_user.php',
    'Views/Users/edit_user.php',
    'Views/Users/profile.php',
    'Controllers/UserController.php',
    'Models/User.php'
];

foreach ($user_files as $file) {
    $full_path = BASE_PATH . $file;
    if (file_exists($full_path)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file does NOT exist</p>";
    }
}

// Test 5: Test syntax of key user files
echo "<h2>Test 5: PHP Syntax Check</h2>";
$files_to_check = [
    BASE_PATH . 'Views/Users/list_users.php',
    BASE_PATH . 'Views/Users/add_user.php',
    BASE_PATH . 'Views/Users/edit_user.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $output = [];
        $return_var = 0;
        exec("c:\\xampp\\php\\php.exe -l \"$file\" 2>&1", $output, $return_var);
        if ($return_var === 0) {
            echo "<p style='color: green;'>✓ " . basename($file) . " - No syntax errors</p>";
        } else {
            echo "<p style='color: red;'>✗ " . basename($file) . " - Syntax errors: " . implode(' ', $output) . "</p>";
        }
    }
}

echo "<h2>Test Results Summary</h2>";
echo "<p>All tests completed. Please review the results above to ensure the user management system is properly configured.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li><a href='Views/Users/index.php'>Go to User Management Dashboard</a></li>";
echo "<li><a href='Views/Users/list_users.php'>View Users List</a></li>";
echo "<li><a href='Views/Users/add_user.php'>Add New User</a></li>";
echo "</ul>";
?>
