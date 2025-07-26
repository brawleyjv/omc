<?php
require_once __DIR__ . '/../../config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php'; // Ensure Database.php is included
require_once BASE_PATH . '/Controllers/UserController.php'; // Ensure UserController.php is included

use MyApp\Models\Database;
use MyApp\Controllers\UserController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Create a Database instance with proper credentials
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
    <title>User Profile - OMC</title>
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
                    <h1>User Profile Management</h1>
                    <p>Search and update user profiles</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/Users/list_users.php" class="nav-link">All Users</a>
                <a href="<?php echo BASE_URL; ?>Views/Users/add_user.php" class="nav-link">Add User</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">User Profile</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/Users/add_user.php" class="btn btn-primary">
                        <span class="icon">👤</span>
                        Add New User
                    </a>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Search User</h2>
                <p class="card-subtitle">Find a user to update their profile information</p>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="search_name" class="form-label">Search by Name</label>
                            <input type="text" id="search_name" name="search_name" class="form-control" 
                                   placeholder="Enter user name..." 
                                   value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>">
                            <small class="form-text">Enter the full or partial name of the user</small>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">🔍</span>
                            Search User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Update Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Update Profile</h2>
                <p class="card-subtitle">Modify user information and settings</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>Views/Users/profile.php" method="post">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name" class="form-label required">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo $name; ?>" required
                                   placeholder="Enter full name...">
                            <small class="form-text">User's complete name</small>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label required">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo $phone; ?>" required
                                   placeholder="Enter phone number...">
                            <small class="form-text">Primary contact number</small>
                        </div>

                        <div class="form-group">
                            <label for="position" class="form-label required">Position</label>
                            <input type="text" id="position" name="position" class="form-control" 
                                   value="<?php echo $position; ?>" required
                                   placeholder="Enter job position...">
                            <small class="form-text">Current job title or role</small>
                        </div>

                        <div class="form-group">
                            <label for="user_type" class="form-label required">User Type</label>
                            <select id="user_type" name="user_type" class="form-control" required>
                                <option value="">Select user type...</option>
                                <option value="admin" <?php if ($user_type == 'admin') echo 'selected'; ?>>Administrator</option>
                                <option value="user" <?php if ($user_type == 'user') echo 'selected'; ?>>Standard User</option>
                                <option value="manager" <?php if ($user_type == 'manager') echo 'selected'; ?>>Manager</option>
                                <option value="supervisor" <?php if ($user_type == 'supervisor') echo 'selected'; ?>>Supervisor</option>
                            </select>
                            <small class="form-text">User access level and permissions</small>
                        </div>

                        <div class="form-group">
                            <label for="date_of_hire" class="form-label required">Date of Hire</label>
                            <input type="date" id="date_of_hire" name="date_of_hire" class="form-control" 
                                   value="<?php echo $date_of_hire; ?>" required>
                            <small class="form-text">Employee start date</small>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Enter new password...">
                            <small class="form-text">Leave blank to keep current password</small>
                        </div>
                    </div>

                    <div class="form-actions mt-6">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">💾</span>
                            Update Profile
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                            <span class="icon">🏠</span>
                            Return to Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>Views/Users/list_users.php" class="btn btn-ghost">
                            <span class="icon">👥</span>
                            View All Users
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
