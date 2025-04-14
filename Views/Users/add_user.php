<?php
require_once BASE_PATH . __DIR__ . '/../../config.php'; // Updated path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">Add User</h1>
        <form action="<?php echo BASE_URL; ?>public/users/insert_user.php" method="post"> <!-- Use BASE_URL -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit" class="btn styled-btn">Submit</button>
        </form>
    </div>
</body>
</html>
