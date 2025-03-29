<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

// Pass the required arguments to the Database constructor
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$controller = new ProjectController($database);

$projectId = $_GET['project_id'] ?? null;

if ($projectId) {
    $project = $controller->getProjectById($projectId);
    if (!$project) {
        echo "<script>alert('Project not found.'); window.location.href='" . BASE_URL . "Views/projects/list_projects.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('No project ID provided.'); window.location.href='" . BASE_URL . "Views/projects/list_projects.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $controller->updateProject(
        $_POST['project_id'],
        $_POST['project_name'],
        $_POST['design_date'],
        $_POST['customer_name'],
        $_POST['laser_time'],
        $_POST['router_time'],
        $_POST['labor_hours'],
        $_POST['project_description'],
        $_POST['due_date'],
        $_POST['file_upload'],
        $_POST['image_upload']
    );

    if ($success) {
        echo "<script>alert('Project updated successfully.'); window.location.href='" . BASE_URL . "Views/projects/list_projects.php';</script>";
    } else {
        echo "<script>alert('Failed to update project.');</script>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css"> <!-- Corrected CSS path -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Corrected header path -->

    <?php if ($project): ?>
        <h1 class="center-title">Edit Project</h1>
        <form id="project-form" action="../../Views/projects/edit_projects.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
            <div class="submit-container" style="justify-content: space-between; width: 100%;">
                <button type="button" class="btn styled-btn" onclick="window.location.href='../../Views/projects/list_projects.php'" style="margin-left: 0;">Cancel</button>
                <input type="submit" class="btn styled-btn" value="Update" style="margin-right: 0;">
            </div>
            <div class="form-container">
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="design_date">Design Date:</label>
                    <input type="date" id="design_date" name="design_date" value="<?php echo htmlspecialchars($project['design_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($project['customer_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="laser_time">Laser Time (minutes):</label>
                    <input type="number" id="laser_time" name="laser_time" value="<?php echo htmlspecialchars($project['laser_time']); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="router_time">Router Time (minutes):</label>
                    <input type="number" id="router_time" name="router_time" value="<?php echo htmlspecialchars($project['router_time']); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="labor_hours">Labor Hours:</label>
                    <input type="number" id="labor_hours" name="labor_hours" value="<?php echo htmlspecialchars($project['labor_hours']); ?>" max="9999">
                </div>
                <div class="form-group">
                    <label for="project_description">Project Description:</label>
                    <textarea id="project_description" name="project_description" rows="10"><?php echo htmlspecialchars($project['project_description']); ?></textarea>
                </div>
                <div class="file-group">
                    <label for="file_upload">File Upload:</label>
                    <input type="file" id="file_upload" name="file_upload[]" multiple>
                    <?php if (!empty($project['file_upload'])): ?>
                        <p>Current files: <?php echo htmlspecialchars($project['file_upload']); ?></p>
                    <?php endif; ?>
                    <label for="image_upload">Image Upload:</label>
                    <input type="file" id="image_upload" name="image_upload" accept=".bmp,.jpg,.jpeg,.tiff,.gif,.png">
                    <?php if (!empty($project['image_upload'])): ?>
                        <p>Current image: <?php echo htmlspecialchars($project['image_upload']); ?></p>
                    <?php endif; ?>
                    <label for="design_file">Design File:</label>
                    <input type="file" id="design_file" name="design_file">
                    <?php if (!empty($project['design_file'])): ?>
                        <p>Current design file: <?php echo htmlspecialchars($project['design_file']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="due_date">Project Due By Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($project['due_date']); ?>">
                </div>
            </div>
        </form>
    <?php else: ?>
        <p>Project not found.</p>
    <?php endif; ?>
</body>
</html>