<?php
require_once __DIR__ . '/../../config.php'; // Load config first to get BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - OMC</title>
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
                    <h1>Add New User</h1>
                    <p>Create a new user account for the OMC system</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/users/index.php" class="nav-link">Users Home</a>
                <a href="<?php echo BASE_URL; ?>Views/users/list_users.php" class="nav-link">All Users</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Create New User</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/users/list_users.php" class="btn btn-ghost">
                        <span class="icon">👤</span>
                        View All Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Add User Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">User Account Information</h2>
                <p class="card-subtitle">Enter the username and password for the new user account</p>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>public/users/insert_user.php" method="post">
                    <div class="form-grid">
                        <!-- Account Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Account Credentials</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" id="username" name="username" class="form-control" required 
                                           placeholder="Enter username" 
                                           pattern="[a-zA-Z0-9_]+" 
                                           title="Username should only contain letters, numbers, and underscores">
                                    <small class="form-text">Username must be unique and contain only letters, numbers, and underscores</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" id="password" name="password" class="form-control" required 
                                           placeholder="Enter secure password"
                                           minlength="6">
                                    <small class="form-text">Password must be at least 6 characters long</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                                           placeholder="Re-enter password">
                                    <small class="form-text">Re-enter the password to confirm</small>
                                </div>
                            </div>
                        </div>

                        <!-- Security Notice -->
                        <div class="form-section">
                            <div class="notification notification-info">
                                <strong>Security Notice:</strong> The password will be securely hashed before storage. Make sure to use a strong password with a mix of letters, numbers, and special characters.
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">👤</span>
                            Create User
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/users/list_users.php" class="btn btn-secondary">
                            <span class="icon">✖️</span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        });
    </script>
</body>
</html>
