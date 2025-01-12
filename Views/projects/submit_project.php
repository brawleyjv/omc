<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Project.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    $controller = new ProjectController($database);

    $project_name = $_POST['project_name'];
    $design_date = $_POST['design_date'];
    $customer_name = $_POST['customer_name'];
    $laser_time = $_POST['laser_time'];
    $router_time = $_POST['router_time'];
    $labor_hours = $_POST['labor_hours'];
    $project_description = $_POST['project_description'];
    $due_date = $_POST['due_date'];

    $project_id = $controller->addProject($project_name, $design_date, $customer_name, $laser_time, $router_time, $labor_hours, $project_description, $due_date);

    if ($project_id) {
        header("Location: /OMC/Views/bom/add_bom.php?project_id=$project_id&project_name=" . urlencode($project_name));
        exit();
    } else {
        echo "<script>alert('Failed to add project. Please try again.'); window.location.href = 'add_project.php';</script>";
    }
}
?>
