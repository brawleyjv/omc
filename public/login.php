<?php
session_start();
require_once __DIR__ . '/../Globals/Config.php';
require_once __DIR__ . '/../Models/Database.php';

use MyApp\Models\Database;
use Globals\Config;

// Establish database connection
$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE name = :name";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) { // Compare plain text password
        $_SESSION['username'] = $name;
        $conn = null; // Close the connection
        header("Location: ../Views/main.php"); // Corrected path
        exit(); // Ensure no further code is executed
    } else {
        $error_message = "Invalid username or password.";
    }

    $conn = null; // Close the connection
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <?php include '../Views/header.php'; // Ensure this path is correct ?>
    <div class="container">
        <h1>Login</h1>
        <img src="../public/images/login-image.png" alt="Login Image" class="login-image"> <!-- Updated image file extension -->
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="" required> <!-- Ensure field is blank -->
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="" required> <!-- Ensure field is blank -->
            <input type="submit" value="Login" class="btn styled-btn"> <!-- Updated button style -->
        </form>
    </div>
</body>
</html>
