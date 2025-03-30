<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Explicitly reference the OMC directory
    include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">Login</h1>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)): ?> <!-- Display error only on failed login -->
            <p class="error" style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="/public/login.php" method="post"> <!-- Point to public login PHP script -->
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Login" class="btn styled-btn">
            </div>
        </form>
        <p>Don't have an account? <a href="/Views/Users/register.php">Register here</a></p> <!-- Link to registration page -->
    </div>
</body>
</html>
