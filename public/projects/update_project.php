<?php
// filepath: /c:/xampp/htdocs/OMC/public/projects/update_project.php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$controller = new ProjectController($database);

$id = $_POST['id'];
$project_name = $_POST['project_name'];
$design_date = $_POST['design_date'];
$customer_name = $_POST['customer_name'];
$laser_time = $_POST['laser_time'];
$router_time = $_POST['router_time'];
$labor_hours = $_POST['labor_hours'];
$project_description = $_POST['project_description'];
$due_date = $_POST['due_date'];

// Fetch existing project data
$project = $controller->getProjectById($id);

$file_upload = !empty($_FILES['file_upload']['name']) ? $_FILES['file_upload']['name'] : $project['file_upload'];
$image_upload = !empty($_FILES['image_upload']['name']) ? $_FILES['image_upload']['name'] : $project['image_upload'];

if (!empty($_FILES['file_upload']['name'])) {
    $file_upload_path = 'C:/xampp/htdocs/OMC/projects/project_files/' . $file_upload;
    $file_upload_path = checkAndRenameFile($file_upload_path);
    move_uploaded_file($_FILES['file_upload']['tmp_name'], $file_upload_path);
    $file_upload = basename($file_upload_path);
}
if (!empty($_FILES['image_upload']['name'])) {
    $image_upload_path = 'C:/xampp/htdocs/OMC/projects/project_images/' . $image_upload;
    $image_upload_path = checkAndRenameFile($image_upload_path);
    move_uploaded_file($_FILES['image_upload']['tmp_name'], $image_upload_path);
    $image_upload = basename($image_upload_path);
}

// Update project with file paths if files were uploaded
$controller->updateProject(
    $id,
    $project_name,
    $design_date,
    $customer_name,
    $laser_time,
    $router_time,
    $labor_hours,
    $project_description,
    $due_date,
    $file_upload,
    $image_upload
);

header('Location: list_projects.php');
exit;

function checkAndRenameFile($filePath) {
    $fileInfo = pathinfo($filePath);
    $dir = $fileInfo['dirname'];
    $filename = $fileInfo['filename'];
    $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';
    $counter = 1;

    while (file_exists($filePath)) {
        $filePath = $dir . '/' . $filename . '_' . $counter . $extension;
        $counter++;
    }

    return $filePath;
}
?>