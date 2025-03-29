<?php
ob_start(); // Start output buffering to prevent premature output

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session only if not already started
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Explicitly reference the OMC directory

// Remove or adjust this line if it causes redirection issues
// require_once BASE_PATH . 'auth/check_auth.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1>Ozark Made Crafts</h1>
                <p>Precision Craftsmanship with a Personal Touch</p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn styled-btn">Home</a>
            </div>
            <div class="user-info">
                <?php if (!empty($_SESSION['username'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <form action="<?php echo BASE_URL; ?>public/logout.php" method="post" style="display: inline;">
                        <button type="submit" class="btn styled-btn">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>Views/Users/login.php" class="btn styled-btn">Login</a>
                    <a href="<?php echo BASE_URL; ?>Views/Users/register.php" class="btn styled-btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
