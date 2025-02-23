<?php
session_start();
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';

use MyApp\Models\Database;
use Globals\Config;

// Establish database connection
$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$conn = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $user_type = $_POST['user_type'];
    $date_of_hire = $_POST['date_of_hire'];
    $password = $_POST['password'];

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check for duplicate name
    $check_sql = "SELECT * FROM users WHERE name=:name";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        $error_message = "The name '$name' is already taken. Please choose a different name.";
    } else {
        $sql = "INSERT INTO users (name, phone, position, user_type, date_of_hire, password) VALUES (:name, :phone, :position, :user_type, :date_of_hire, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindValue(':position', $position, PDO::PARAM_STR);
        $stmt->bindValue(':user_type', $user_type, PDO::PARAM_STR);
        $stmt->bindValue(':date_of_hire', $date_of_hire, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->execute();

        $_SESSION['username'] = $name;
        $conn = null; // Close the connection
        header("Location: ../login.php"); // Redirect to login.php after successful registration
        exit();
    }
}
?>

