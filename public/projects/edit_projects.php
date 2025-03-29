<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$controller = new ProjectController($database);

// Check if the project name is provided in the GET or POST request
$project_name = $_GET['project_name'] ?? $_POST['project_name'] ?? '';
if (empty($project_name)) {
    echo "<script>alert('Project name is missing.'); window.location.href = '" . BASE_URL . "Views/projects/list_projects.php';</script>";
    exit;
}

// Retrieve the project by name
$project = $controller->getProjectByName($project_name);
if (!$project) {
    echo "<script>alert('Project not found.'); window.location.href = '" . BASE_URL . "Views/projects/list_projects.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    $file_uploads = !empty($_FILES['file_upload']['name'][0]) ? $_FILES['file_upload']['name'] : (!empty($project['file_upload']) ? explode(',', $project['file_upload']) : []);
    $image_upload = !empty($_FILES['image_upload']['name']) ? $_FILES['image_upload']['name'] : $project['image_upload'];
    $design_file = !empty($_FILES['design_file']['name']) ? $_FILES['design_file']['name'] : $project['design_file'];

    $upload_dir = BASE_PATH . '/projects/project_files/' . $project_name . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES['file_upload']['name'][0])) {
        $file_upload_paths = [];
        foreach ($_FILES['file_upload']['name'] as $key => $name) {
            $tmp_name = $_FILES['file_upload']['tmp_name'][$key];
            $file_upload_path = $upload_dir . $name;
            move_uploaded_file($tmp_name, $file_upload_path);
            $file_upload_paths[] = basename($file_upload_path);
        }
        $file_uploads = implode(',', $file_upload_paths);
    } else {
        $file_uploads = is_array($file_uploads) ? implode(',', $file_uploads) : $file_uploads;
    }

    if (!empty($_FILES['image_upload']['name'])) {
        $image_upload_path = $upload_dir . $image_upload;
        move_uploaded_file($_FILES['image_upload']['tmp_name'], $image_upload_path);
        $image_upload = basename($image_upload_path);
    }

    if (!empty($_FILES['design_file']['name'])) {
        $design_file_path = $upload_dir . $design_file;
        move_uploaded_file($_FILES['design_file']['tmp_name'], $design_file_path);
        $design_file = basename($design_file_path);
    }

    // Update the project
    $controller->updateProject(
        $project_name, // This is the project ID or name used in the WHERE clause
        $_POST['project_name'], // New project name
        $_POST['design_date'],
        $_POST['customer_name'],
        $_POST['laser_time'],
        $_POST['router_time'],
        $_POST['labor_hours'],
        $_POST['project_description'],
        $_POST['due_date'],
        $file_uploads,
        $image_upload
    );

    header('Location: ' . BASE_URL . 'Views/projects/list_projects.php');
    exit;
}

// Pass the project data to the HTML view
require_once BASE_PATH . '/Views/projects/edit_project.php';
?>
