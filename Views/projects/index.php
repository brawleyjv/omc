<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
require_once BASE_PATH . '/Models/Database.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Controllers/ProjectController.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Controllers/InstallController.php'; // Updated to use BASE_PATH
require_once BASE_PATH . '/Models/Settings.php'; // Updated to use BASE_PATH

use Models\Settings; // Import the Settings class
use MyApp\Models\Database; // Import the Database class
use MyApp\Controllers\ProjectController; // Ensure ProjectController is included

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session is started only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch settings
$settings = new Settings();
$settings_data = $settings->getSettings();
$_SESSION['company_name'] = $settings_data['company_name'];
$_SESSION['company_slogan'] = $settings_data['company_slogan'];

try {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure proper initialization
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    $db = $database->getConnection(); // Ensure $db is a PDO instance
    $projectController = new ProjectController($db); // Pass the PDO instance to the controller
    $projects = $projectController->getAllProjects(); // Fetch all projects
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    die("An error occurred while connecting to the database.");
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        header("Location: " . BASE_URL . "Views/projects/index.php?error=Name is required"); // Updated to use BASE_URL
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE name = :name");
    if (!$stmt) {
        error_log("Prepared statement failed: " . $conn->errorInfo()[2]);
        header("Location: " . BASE_URL . "Views/projects/index.php?error=An unexpected error occurred"); // Updated to use BASE_URL
        exit();
    }

    $stmt->bindParam(':name', $name);

    if ($stmt->execute()) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $_SESSION['username'] = $name;
            header("Location: " . BASE_URL . "Views/main.php"); // Updated to use BASE_URL
            exit();
        } else {
            header("Location: " . BASE_URL . "Views/Users/register.php?name=" . urlencode($name)); // Updated to use BASE_URL
            exit();
        }
    } else {
        error_log("Database error: " . $stmt->errorInfo()[2]);
        header("Location: " . BASE_URL . "Views/projects/index.php?error=An unexpected error occurred"); // Updated to use BASE_URL
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../../Views/header.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <div class="header-content">
                <h1>Project Management</h1>
                <p class="text-muted">Manage your projects, estimates, and calculations</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                    <span class="icon">🏠</span>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add Project Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">➕</span>
                    </div>
                    <h3 class="card-title">Add New Project</h3>
                    <p class="text-muted mb-4">Create a new project with details and specifications</p>
                    <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="btn btn-primary w-full">
                        <span class="icon">📋</span>
                        Add Project
                    </a>
                </div>
            </div>

            <!-- List Projects Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📋</span>
                    </div>
                    <h3 class="card-title">View All Projects</h3>
                    <p class="text-muted mb-4">Browse and manage all projects in your system</p>
                    <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-primary w-full">
                        <span class="icon">👁️</span>
                        View Projects
                    </a>
                </div>
            </div>

            <!-- Search Projects Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">🔍</span>
                    </div>
                    <h3 class="card-title">Search Projects</h3>
                    <p class="text-muted mb-4">Find specific projects by name, customer, or description</p>
                    <a href="<?php echo BASE_URL; ?>Views/search_projects.php" class="btn btn-primary w-full">
                        <span class="icon">🔍</span>
                        Search Projects
                    </a>
                </div>
            </div>

            <!-- Board Feet Calculator Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">📏</span>
                    </div>
                    <h3 class="card-title">Board Feet Calculator</h3>
                    <p class="text-muted mb-4">Calculate board feet for lumber projects</p>
                    <a href="<?php echo BASE_URL; ?>Views/projects/boardfeet.php" class="btn btn-primary w-full">
                        <span class="icon">🧮</span>
                        Calculator
                    </a>
                </div>
            </div>

            <!-- Estimate Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">💰</span>
                    </div>
                    <h3 class="card-title">Project Estimates</h3>
                    <p class="text-muted mb-4">Create and manage project cost estimates</p>
                    <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="btn btn-primary w-full">
                        <span class="icon">💰</span>
                        Create Estimate
                    </a>
                </div>
            </div>

            <!-- View Project Card -->
            <div class="card hover-card">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-4">
                        <span class="icon text-4xl">👁️</span>
                    </div>
                    <h3 class="card-title">View Project Details</h3>
                    <p class="text-muted mb-4">View detailed information about a specific project</p>
                    <a href="<?php echo BASE_URL; ?>Views/projects/view_project.php" class="btn btn-primary w-full">
                        <span class="icon">📄</span>
                        View Details
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mt-6">
            <div class="card-header">
                <h2>Project Overview</h2>
                <p class="text-muted">Quick statistics about your projects</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div class="stats-card">
                            <div class="stats-number"><?php echo count($projects); ?></div>
                            <div class="stats-label">Total Projects</div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="stats-card">
                            <div class="stats-number">
                                <?php 
                                $active_projects = array_filter($projects, function($p) { 
                                    return empty($p['completed_date']); 
                                });
                                echo count($active_projects); 
                                ?>
                            </div>
                            <div class="stats-label">Active Projects</div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Recent Projects</h4>
                        <?php if (!empty($projects)): ?>
                            <div class="space-y-2">
                                <?php foreach (array_slice($projects, 0, 3) as $project): ?>
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm"><?php echo htmlspecialchars($project['project_name']); ?></span>
                                        <span class="text-xs text-muted">
                                            <?php echo date('M j', strtotime($project['design_date'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-sm">No projects created yet.</p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="text-primary text-sm">View all projects →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close the PDO connection
$conn = null;
?>