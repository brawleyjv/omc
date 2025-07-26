<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
require_once BASE_PATH . '/Models/Database.php'; // Now BASE_PATH is available

use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getConnection(); // Retrieve the PDO instance
if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

$stmt = $conn->prepare("SELECT * FROM users"); // Use prepare instead of query
$stmt->execute(); // Execute the prepared statement
$users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all users
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>User Management</h1>
                    <p>Manage system users and permissions</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/Users/index.php" class="nav-link">User Profile</a>
                <a href="<?php echo BASE_URL; ?>Views/Users/add_user.php" class="nav-link">Add User</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">System Users</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/Users/add_user.php" class="btn btn-primary">
                        <span class="icon">👤</span>
                        Add New User
                    </a>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <?php if (!empty($users)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Users</h2>
                    <p class="card-subtitle">Total: <?php echo count($users); ?> users</p>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Name</th>
                                <th>User Type</th>
                                <th>Position</th>
                                <th>Date of Hire</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <span class="font-mono text-sm"><?php echo htmlspecialchars($user['id']); ?></span>
                                    </td>
                                    <td>
                                        <div class="font-semibold"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $user_type = $user['user_type'] ?? 'user';
                                        $badge_class = $user_type === 'admin' ? 'badge-danger' : 
                                                      ($user_type === 'manager' ? 'badge-warning' : 'badge-info');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($user_type); ?></span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($user['position'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($user['date_of_hire'])) {
                                            echo date('M j, Y', strtotime($user['date_of_hire']));
                                        } else {
                                            echo '<span class="text-muted">Not set</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>Views/Users/edit_user.php?user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-secondary">
                                                <span class="icon">✏️</span>
                                                Edit
                                            </a>
                                            <button onclick="confirmDelete(<?php echo $user['id']; ?>)" 
                                                    class="btn btn-sm btn-danger">
                                                <span class="icon">🗑️</span>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-6xl">👥</span>
                    </div>
                    <h3 class="card-title">No Users Found</h3>
                    <p class="text-muted mb-6">There are no users in the system yet.</p>
                    <a href="<?php echo BASE_URL; ?>Views/Users/add_user.php" class="btn btn-primary">
                        <span class="icon">➕</span>
                        Add Your First User
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                // Create and submit form
                var form = document.createElement('form');
                form.method = 'post';
                form.action = '<?php echo BASE_URL; ?>public/Users/delete_user.php';
                
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_id';
                input.value = userId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
