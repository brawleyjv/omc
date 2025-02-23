<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/User.php';

use MyApp\Models\Database;
use MyApp\Models\User;
use Globals\Config;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    $user = new User($database);

    if ($user->login($username, $password)) {
        $_SESSION['username'] = $username;
        error_log("Login successful for user: $username"); // Log successful login
        header('Location: /OMC/Views/main.php');
        exit();
    } else {
        $error = "Invalid username or password.";
        error_log("Login failed for user: $username"); // Log failed login
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/OMC/public/css/styles.css">
</head>
<body>
    <div class="container">
        <h1 class="title">Login</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
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
    </div>
</body>
</html>
