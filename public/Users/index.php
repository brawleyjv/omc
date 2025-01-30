<?php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
// require_once __DIR__ . '/../../Controllers/UserController.php';
use MyApp\Models\Database;
// use MyApp\Controllers\UserController;

// Ensure Database is instantiated with required arguments
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$conn = $database->getConnection(); // Get the connection

$name = '';
$phone = '';
$position = '';
$user_type = '';
$date_of_hire = '';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM users WHERE name=:username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) { // Use rowCount() to check the number of rows
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = htmlspecialchars($row['name']);
        $phone = htmlspecialchars($row['phone']);
        $position = htmlspecialchars($row['position']);
        $user_type = htmlspecialchars($row['user_type']);
        $date_of_hire = htmlspecialchars($row['date_of_hire']);
    }
}

$conn = null; // Close the connection
?>
