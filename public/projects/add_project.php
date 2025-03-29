<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$controller = new ProjectController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = $_POST['project_name'] ?? '';
    $design_date = $_POST['design_date'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $laser_time = $_POST['laser_time'] ?? 0;
    $router_time = $_POST['router_time'] ?? 0;
    $labor_hours = $_POST['labor_hours'] ?? 0;
    $project_description = $_POST['project_description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    $file_uploads = [];
    $image_uploads = [];
    $design_files = [];
    $upload_dir = BASE_PATH . 'projects/project_files/' . $project_name . '/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach (['file_upload', 'image_upload', 'design_file'] as $type) {
        if (!empty($_FILES[$type]['name'][0])) {
            foreach ($_FILES[$type]['name'] as $key => $name) {
                $tmp_name = $_FILES[$type]['tmp_name'][$key];
                $file_path = $upload_dir . $name;
                if (move_uploaded_file($tmp_name, $file_path)) {
                    ${$type . 's'}[] = basename($file_path);
                } else {
                    echo "Failed to upload $type: $name";
                    exit;
                }
            }
        }
    }

    try {
        $controller->addProject(
            $project_name,
            $design_date,
            $customer_name,
            $laser_time,
            $router_time,
            $labor_hours,
            $project_description,
            $due_date,
            implode(',', $file_uploads),
            implode(',', $image_uploads),
            implode(',', $design_files)
        );
        header('Location: ' . BASE_URL . 'Views/projects/view_project.php?project_name=' . urlencode($project_name));
        exit;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "<script>
                alert('A project with this name already exists. Please choose a different name.');
                window.location.href = '" . BASE_URL . "Views/projects/add_project.php';
            </script>";
        } else {
            echo 'Failed to add project: ', $e->getMessage();
        }
    }
} else {
    header('Location: ' . BASE_URL . 'Views/projects/add_project.php');
    exit();
}
?>