<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated path
require_once BASE_PATH . '/Models/Database.php'; // Ensure consistent path
require_once BASE_PATH . '/Controllers/UserController.php';

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

    // Hash the password only if it is provided
    $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    $sql = "UPDATE users SET phone=:phone, position=:position, user_type=:user_type, date_of_hire=:date_of_hire";
    if ($hashed_password) {
        $sql .= ", password=:password";
    }
    $sql .= " WHERE name=:name";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':position', $position);
    $stmt->bindParam(':user_type', $user_type);
    $stmt->bindParam(':date_of_hire', $date_of_hire);
    if ($hashed_password) {
        $stmt->bindParam(':password', $hashed_password);
    }
    $stmt->bindParam(':name', $name);

    if ($stmt->execute()) {
        echo "<script>
                alert('Profile updated successfully');
                window.location.href = '" . BASE_URL . "/views/main.php'; // Ensure correct BASE_URL
              </script>";
    } else {
        echo "Error: " . $stmt->errorInfo()[2]; // Use errorInfo() to get the error message
    }

    $conn = null; // Close the connection
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Ensure correct BASE_URL -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>
