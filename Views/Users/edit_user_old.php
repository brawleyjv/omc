<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
require_once BASE_PATH . '/Models/Database.php'; // Now BASE_PATH is available

use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getConnection(); // Retrieve the PDO instance
if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}
$user = null;

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id"); // Use prepare and bindParam
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user data
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: " . BASE_URL . "Views/users/list_users.php"); // Use BASE_URL
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">Edit User</h1>
        <?php if ($user): ?>
            <form action="<?php echo BASE_URL; ?>Views/users/edit_user.php" method="post"> <!-- Use BASE_URL -->
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>
                <button type="submit" class="btn styled-btn">Update</button>
            </form>
        <?php else: ?>
            <p>User not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
