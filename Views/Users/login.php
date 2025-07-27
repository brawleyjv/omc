<?php
// Flexible path resolution for config.php to handle different server structures
function findConfig($startDir) {
    $paths = [
        $startDir . '/../../config.php',              // Standard: Views/Users -> root
        $startDir . '/../../../config.php',           // Nested: omc/omc/Views/Users -> omc/config.php  
        $startDir . '/../../../../config.php',        // Deep nested
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Search up directory tree as fallback
    $current = $startDir;
    for ($i = 0; $i < 6; $i++) {
        if (file_exists($current . '/config.php')) {
            return $current . '/config.php';
        }
        $current = dirname($current);
    }
    
    die("Error: config.php not found");
}

require_once findConfig(__DIR__);

// Log session details for debugging
error_log("Login.php: Session username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : "Not set"));

// Redirect to the main page if the user is already logged in
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    error_log("Login.php: Redirecting to main.php");
    header("Location: " . BASE_URL . "Views/main.php");
    exit();
}

// Include the header (ensure it has no redirection logic)
include BASE_PATH . '/Views/header.php'; // Ensure correct path

// If not logged in, display the login page
error_log("Login.php: Displaying login page.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Removed leading slash -->
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>public/Users/login_handler.php" method="post"> <!-- Removed leading slash -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
