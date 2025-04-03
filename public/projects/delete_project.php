<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

$database = new Database();
$db = $database->getConnection(); // Ensure $db is a PDO instance
$projectController = new ProjectController($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'] ?? null;

    if (!$project_id) {
        echo "<script>alert('Project ID is missing.'); window.history.back();</script>";
        exit;
    }

    try {
        $success = $projectController->deleteProject($project_id);

        if ($success) {
            echo "<script>alert('Project deleted successfully.'); window.location.href = '" . BASE_URL . "Views/projects/list_projects.php';</script>";
        } else {
            echo "<script>alert('Failed to delete project.'); window.history.back();</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <h1>Delete Project</h1>
    <!-- Add your content here -->
</body>
</html>
