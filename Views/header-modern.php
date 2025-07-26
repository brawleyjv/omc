<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Ensure session_start() is called only if a session is not already active
}

ob_start(); // Start output buffering to prevent premature output

require_once realpath(dirname(__FILE__) . '/../config.php'); // Ensure correct path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <img src="<?php echo BASE_URL; ?>public/images/login-image.png" alt="OMC Logo" onerror="this.style.display='none'">
                <div class="header-brand-text">
                    <h1><?php echo isset($_SESSION['company_name']) ? htmlspecialchars($_SESSION['company_name']) : 'Ozark Made Crafts'; ?></h1>
                    <p><?php echo isset($_SESSION['company_slogan']) ? htmlspecialchars($_SESSION['company_slogan']) : 'Precision Craftsmanship with a Personal Touch'; ?></p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/index.php" class="nav-link">Projects</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/index.php" class="nav-link">Materials</a>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="nav-link">Customers</a>
                <div class="user-info">
                    <?php if (!empty($_SESSION['username'])): ?>
                        <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <form action="<?php echo BASE_URL; ?>public/logout.php" method="post" style="display: inline;">
                            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>Views/Users/login.php" class="btn btn-outline btn-sm">Login</a>
                        <a href="<?php echo BASE_URL; ?>Views/Users/register.php" class="btn btn-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
</body>
</html>
