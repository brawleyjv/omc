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
$success_message = '';
$error_message = '';

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id"); // Use prepare and bindParam
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user data
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $position = $_POST['position'] ?? '';
        $user_type = $_POST['user_type'] ?? 'user';
        $date_of_hire = $_POST['date_of_hire'] ?? null;
        
        // Prepare SQL based on whether password is being updated
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password, name = :name, phone = :phone, position = :position, user_type = :user_type, date_of_hire = :date_of_hire WHERE id = :id");
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = :username, name = :name, phone = :phone, position = :position, user_type = :user_type, date_of_hire = :date_of_hire WHERE id = :id");
        }
        
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':position', $position, PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
        $stmt->bindParam(':date_of_hire', $date_of_hire, PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            header("Location: " . BASE_URL . "Views/Users/list_users.php?success=" . urlencode('User updated successfully'));
            exit();
        } else {
            $error_message = 'Failed to update user.';
        }
    } catch (Exception $e) {
        $error_message = 'An error occurred: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - OMC</title>
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
                    <h1>Edit User</h1>
                    <p>Update user information and settings</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/Users/index.php" class="nav-link">User Profile</a>
                <a href="<?php echo BASE_URL; ?>Views/Users/list_users.php" class="nav-link">All Users</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Edit User</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/Users/list_users.php" class="btn btn-secondary">
                        <span class="icon">👥</span>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <div class="notification notification-error">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <?php if ($user): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Update User Information</h2>
                    <p class="card-subtitle">Modify user details and permissions</p>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>Views/Users/edit_user.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="form-group">
                                <label for="username" class="form-label required">Username</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required
                                       placeholder="Enter username...">
                                <small class="form-text">Login username for the system</small>
                            </div>

                            <div class="form-group">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                                       placeholder="Enter full name...">
                                <small class="form-text">User's complete name</small>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       placeholder="Enter phone number...">
                                <small class="form-text">Primary contact number</small>
                            </div>

                            <div class="form-group">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" id="position" name="position" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['position'] ?? ''); ?>"
                                       placeholder="Enter job position...">
                                <small class="form-text">Current job title or role</small>
                            </div>

                            <div class="form-group">
                                <label for="user_type" class="form-label">User Type</label>
                                <select id="user_type" name="user_type" class="form-control">
                                    <option value="user" <?php if (($user['user_type'] ?? '') == 'user') echo 'selected'; ?>>Standard User</option>
                                    <option value="admin" <?php if (($user['user_type'] ?? '') == 'admin') echo 'selected'; ?>>Administrator</option>
                                    <option value="manager" <?php if (($user['user_type'] ?? '') == 'manager') echo 'selected'; ?>>Manager</option>
                                    <option value="supervisor" <?php if (($user['user_type'] ?? '') == 'supervisor') echo 'selected'; ?>>Supervisor</option>
                                </select>
                                <small class="form-text">User access level and permissions</small>
                            </div>

                            <div class="form-group">
                                <label for="date_of_hire" class="form-label">Date of Hire</label>
                                <input type="date" id="date_of_hire" name="date_of_hire" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['date_of_hire'] ?? ''); ?>">
                                <small class="form-text">Employee start date</small>
                            </div>

                            <div class="form-group md:col-span-2">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" id="password" name="password" class="form-control"
                                       placeholder="Enter new password...">
                                <small class="form-text">Leave blank to keep current password</small>
                            </div>
                        </div>

                        <div class="form-actions mt-6">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon">💾</span>
                                Update User
                            </button>
                            <a href="<?php echo BASE_URL; ?>Views/Users/list_users.php" class="btn btn-secondary">
                                <span class="icon">❌</span>
                                Cancel
                            </a>
                            <button type="button" onclick="confirmDelete(<?php echo $user['id']; ?>)" class="btn btn-danger">
                                <span class="icon">🗑️</span>
                                Delete User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-12">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-6xl">❌</span>
                    </div>
                    <h3 class="card-title">User Not Found</h3>
                    <p class="text-muted mb-6">The requested user could not be found.</p>
                    <a href="<?php echo BASE_URL; ?>Views/Users/list_users.php" class="btn btn-primary">
                        <span class="icon">👥</span>
                        Back to Users List
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
