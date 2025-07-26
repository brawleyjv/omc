<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getConnection();
    
    echo "<h2>Adding Username Column to Users Table</h2>";
    
    // Check if username column already exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM users LIKE 'username'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "<p style='color: orange;'>Username column already exists!</p>";
    } else {
        echo "<p>Adding username column...</p>";
        
        // Add username column
        $stmt = $conn->prepare("ALTER TABLE users ADD COLUMN username VARCHAR(100) UNIQUE AFTER id");
        $stmt->execute();
        echo "<p style='color: green;'>✓ Username column added</p>";
        
        // Update existing users with usernames based on their names
        $stmt = $conn->prepare("UPDATE users SET username = LOWER(REPLACE(name, ' ', '')) WHERE username IS NULL");
        $stmt->execute();
        echo "<p style='color: green;'>✓ Existing users updated with usernames</p>";
        
        // Handle any null usernames
        $stmt = $conn->prepare("UPDATE users SET username = CONCAT('user', id) WHERE username IS NULL OR username = ''");
        $stmt->execute();
        echo "<p style='color: green;'>✓ All users now have usernames</p>";
    }
    
    // Show updated structure
    echo "<h3>Updated Table Structure:</h3>";
    $stmt = $conn->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show updated user data
    echo "<h3>Updated User Data:</h3>";
    $stmt = $conn->prepare("SELECT id, username, name, user_type FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>User Type</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['user_type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>✅ Migration Complete!</h3>";
    echo "<p>The username column has been successfully added to the users table.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
