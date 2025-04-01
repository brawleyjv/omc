<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php

require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Project.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

// Instantiate the Database class with required arguments
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed
$controller = new ProjectController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = $_POST['project_name'];
    $design_date = $_POST['design_date'];
    $customer_name = $_POST['customer_name'];
    $laser_time = $_POST['laser_time'];
    $router_time = $_POST['router_time'];
    $labor_hours = $_POST['labor_hours'];
    $project_description = $_POST['project_description'];
    $due_date = $_POST['due_date'];

    $file_uploads = [];
    $upload_dir = 'C:/xampp/htdocs/projects/project_files/' . $project_name . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    foreach ($_FILES['file_upload']['name'] as $key => $name) {
        $tmp_name = $_FILES['file_upload']['tmp_name'][$key];
        $file_path = $upload_dir . $name;
        if (move_uploaded_file($tmp_name, $file_path)) {
            $file_uploads[] = $name;
        }
    }
    $file_uploads = implode(',', $file_uploads);

    $image_uploads = [];
    foreach ($_FILES['image_upload']['name'] as $key => $name) {
        $tmp_name = $_FILES['image_upload']['tmp_name'][$key];
        $image_path = $upload_dir . $name;
        if (move_uploaded_file($tmp_name, $image_path)) {
            $image_uploads[] = $name;
        }
    }
    $image_uploads = implode(',', $image_uploads);

    $design_file = $_FILES['design_file']['name'];
    $design_file_path = $upload_dir . $design_file;
    move_uploaded_file($_FILES['design_file']['tmp_name'], $design_file_path);

    $project_id = $controller->addProject($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date, $file_uploads, $image_uploads, $design_file);

    if ($project_id) {
        header("Location: " . BASE_URL . "Views/bom/add_bom.php?project_id=$project_id&project_name=" . urlencode($project_name) . "&customer_name=" . urlencode($customer_name));
        exit();
    } else {
        echo "<script>alert('Failed to add project. Please try again.'); window.location.href = '" . BASE_URL . "Views/projects/add_project.php';</script>";
    }
}
?>
