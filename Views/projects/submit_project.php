<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Correct relative path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/Project.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Use correct config values
    $controller = new ProjectController($database);

    $project_name = $_POST['project_name'];
    $design_date = $_POST['design_date'];
    $customer_name = $_POST['customer_name'];
    $laser_time = $_POST['laser_time'];
    $router_time = $_POST['router_time'];
    $labor_hours = $_POST['labor_hours'];
    $project_description = $_POST['project_description'];
    $due_date = $_POST['due_date'];

    // Handle file uploads
    $file_uploads = !empty($_FILES['file_upload']['name'][0]) ? implode(',', $_FILES['file_upload']['name']) : '';
    $image_uploads = !empty($_FILES['image_upload']['name'][0]) ? implode(',', $_FILES['image_upload']['name']) : '';
    $design_files = !empty($_FILES['design_file']['name'][0]) ? implode(',', $_FILES['design_file']['name']) : '';

    // Pass all 11 arguments to the addProject() method
    try {
        $project_id = $controller->addProject(
            $project_name,
            $design_date,
            $customer_name,
            $laser_time,
            $router_time,
            $labor_hours,
            $project_description,
            $due_date,
            $file_uploads,
            $image_uploads,
            $design_files
        );

        if ($project_id) {
            // Redirect to the project view page
            header("Location: " . BASE_URL . "Views/projects/view_project.php?project_name=" . urlencode($project_name));
            exit();
        } else {
            echo "<script>alert('Failed to add project. Please try again.'); window.location.href = '" . BASE_URL . "public/projects/add_project.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href = '" . BASE_URL . "public/projects/add_project.php';</script>";
    }
}
?>
