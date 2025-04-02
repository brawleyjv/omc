<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Ensure Database.php is included
require_once BASE_PATH . '/Controllers/UserController.php'; // Ensure UserController.php is included

use MyApp\Models\Database;
use MyApp\Controllers\UserController;

$database = new Database(); // Create a Database instance
$db = $database->getConnection(); // Retrieve the PDO instance
if (!$db) {
    error_log("Failed to establish a database connection.");
    header("Location: index.php?error=Database connection failed");
    exit();
}
$userController = new UserController($db); // Pass the PDO instance to UserController

include BASE_PATH . '/Views/header.php'; // Ensure correct path

$name = '';
$phone = '';
$position = '';
$user_type = '';
$date_of_hire = '';

// Handle search functionality
if (isset($_GET['search_name'])) {
    $search_name = $_GET['search_name'];
    if ($db) {
        $stmt = $db->prepare("SELECT * FROM users WHERE name LIKE :search_name LIMIT 1");
        if (!$stmt) {
            error_log("Prepared statement failed: " . $db->errorInfo()[2]);
            header("Location: index.php?error=An unexpected error occurred");
            exit();
        }
        $stmt->bindValue(':search_name', '%' . $search_name . '%', PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $name = htmlspecialchars($row['name']);
            $phone = htmlspecialchars($row['phone']);
            $position = htmlspecialchars($row['position']);
            $user_type = htmlspecialchars($row['user_type']);
            $date_of_hire = htmlspecialchars($row['date_of_hire']);
        } else {
            echo "<script>alert('No user found with that name.');</script>";
        }
    } else {
        error_log("Database connection is null.");
        header("Location: index.php?error=Database connection failed");
        exit();
    }
}

$db = null; // Close the connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Removed leading slash -->
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>
        <p>Search for a user and update their profile.</p>
        <form action="" method="get">
            <label for="search_name">Search by Name:</label>
            <input type="text" id="search_name" name="search_name" placeholder="Enter user name">
            <button type="submit" class="btn styled-btn">Search</button>
        </form>
        <form action="<?php echo BASE_URL; ?>Views/Users/profile.php" method="post"> <!-- Corrected path -->
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>" required>
            <label for="position">Position:</label>
            <input type="text" id="position" name="position" value="<?php echo $position; ?>" required>
            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="admin" <?php if ($user_type == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="user" <?php if ($user_type == 'user') echo 'selected'; ?>>User</option>
            </select>
            <label for="date_of_hire">Date of Hire:</label>
            <input type="date" id="date_of_hire" name="date_of_hire" value="<?php echo $date_of_hire; ?>" required>
            <label for="password">Password (leave blank to keep current password):</label>
            <input type="password" id="password" name="password">
            <input type="submit" value="Update Profile" class="btn styled-btn">
        </form>
        <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn styled-btn red">Return to Main</a> <!-- Corrected path -->
    </div>
</body>
</html>
