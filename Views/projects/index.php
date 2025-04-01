<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated to use realpath(dirname(__FILE__))
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

    $projectController = new ProjectController($database); // Ensure ProjectController is instantiated
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
    <title>Projects Main</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css"> <!-- Corrected CSS path -->
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../../Views/header.php'); ?> <!-- Updated to use realpath -->
    <div class="container">
        <h1 class="title">Ozark Made Project Management System</h1>
        <h1 class="title">Projects Menu</h1>
        <div class="button-container">
            <a href="<?php echo BASE_URL; ?>Views/projects/add_project.php" class="btn styled-btn">Add Project</a>
            <a href="<?php echo BASE_URL; ?>Views/projects/boardfeet.php" class="btn styled-btn">Board Feet</a>
            <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn styled-btn">List Projects</a>
            <a href="<?php echo BASE_URL; ?>Views/projects/search_projects.php" class="btn styled-btn">Search Projects</a>
            <a href="<?php echo BASE_URL; ?>Views/estimate/estimate.php" class="btn styled-btn">Estimate</a>
            <a href="<?php echo BASE_URL; ?>Views/projects/view_project.php" class="btn styled-btn">View Project</a>
        </div>
    </div>
</body>
</html>
<?php
// Close the PDO connection
$conn = null;
?>