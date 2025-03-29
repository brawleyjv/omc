<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Updated path to include /OMC

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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Added leading slash -->
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>/public/Users/login_handler.php" method="post"> <!-- Added leading slash -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
