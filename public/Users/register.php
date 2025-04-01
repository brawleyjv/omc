<?php
session_start();
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated path
require_once BASE_PATH . '/Models/Database.php'; // Ensure consistent path

use MyApp\Models\Database;

// Establish database connection using the correct constructor
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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
        header("Location: " . BASE_URL . "/Views/Users/login.php"); // Ensure correct BASE_URL
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Ensure correct BASE_URL -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>

