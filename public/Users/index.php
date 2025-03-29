<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Use $_SERVER['DOCUMENT_ROOT'] for config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/UserController.php';

use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

try {
    $conn = $database->getConnection(); // Ensure the connection is established
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage()); // Handle connection errors
}

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path -->
</head>
<body>
</body>
</html>
