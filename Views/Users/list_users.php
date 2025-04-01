<?php
require_once BASE_PATH . __DIR__ . '/../../config.php'; // Updated path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Use BASE_PATH

use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$users = $database->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Users</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">List of Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <a href="<?php echo rtrim(BASE_URL, '/'); ?>/Views/users/edit_user.php?user_id=<?php echo $user['id']; ?>" class="btn styled-btn">Edit</a>
                            <form action="<?php echo rtrim(BASE_URL, '/'); ?>/public/users/delete_user.php" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="submit" class="btn styled-btn red" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
