<?php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
// require_once __DIR__ . '/../../Controllers/UserController.php';
use MyApp\Models\Database;
// use MyApp\Controllers\UserController;

// Ensure Database is instantiated with required arguments
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);

$name = '';
$phone = '';
$position = '';
$user_type = '';;
$date_of_hire = '';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM users WHERE name='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = htmlspecialchars($row['name']);
        $phone = htmlspecialchars($row['phone']);
        $position = htmlspecialchars($row['position']);
        $user_type = htmlspecialchars($row['user_type']);
        $date_of_hire = htmlspecialchars($row['date_of_hire']);
    }
}
?>
