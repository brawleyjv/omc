<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed
$projectController = new ProjectController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'] ?? null;
    $projectName = $_POST['project_name'] ?? '';
    $designDate = $_POST['design_date'] ?? '';
    $customerName = $_POST['customer_name'] ?? '';
    $laserTime = $_POST['laser_time'] ?? '';
    $routerTime = $_POST['router_time'] ?? '';
    $laborHours = $_POST['labor_hours'] ?? '';
    $projectDescription = $_POST['project_description'] ?? '';
    $dueDate = $_POST['due_date'] ?? '';
    $fileUpload = $_POST['file_upload'] ?? '';
    $imageUpload = $_POST['image_upload'] ?? '';

    $success = $projectController->updateProject(
        $projectId,
        $projectName,
        $designDate,
        $customerName,
        $laserTime,
        $routerTime,
        $laborHours,
        $projectDescription,
        $dueDate,
        $fileUpload,
        $imageUpload
    );

    if ($success) {
        echo "<script>alert('Project updated successfully.'); window.location.href='" . BASE_URL . "public/projects/list_projects.php';</script>";
    } else {
        echo "<script>alert('Failed to update project.'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>