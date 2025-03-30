<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure session_start() is called only if a session is not already active
}

ob_start(); // Start output buffering to prevent premature output

require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Explicitly reference the OMC directory

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
            <header style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <img src="<?php echo BASE_URL; ?>public/images/login-image.png" alt="Logo" style="height: 50px;">
                    <div>
                        <h1>Ozark Made Crafts</h1>
                        <p>Precision Craftsmanship with a Personal Touch</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 20px; margin-left: 220px;">
                    <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn styled-btn">Home</a>
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
            </header>
        </div>
    </div>
</body>
</html>
