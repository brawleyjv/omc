<?php
session_start();
require_once realpath(dirname(__FILE__) . '/config.php'); // Ensure BASE_URL is available
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Use BASE_URL -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div>
                <h1><?php echo isset($_SESSION['company_name']) ? htmlspecialchars($_SESSION['company_name']) : 'Company Name'; ?></h1>
                <p><?php echo isset($_SESSION['company_slogan']) ? htmlspecialchars($_SESSION['company_slogan']) : 'Company Slogan'; ?></p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>/main.php" class="button">Home</a> <!-- Use BASE_URL -->
                <a href="<?php echo BASE_URL; ?>/about.php" class="button">About</a> <!-- Use BASE_URL -->
                <?php if (isset($_SESSION['username'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="button">Logout</a> <!-- Use BASE_URL -->
                <?php else: ?>
                    <form action="<?php echo BASE_URL; ?>/index.php" method="post" style="display: inline;"> <!-- Use BASE_URL -->
                        <input type="hidden" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        <input type="submit" value="Login" class="button">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
