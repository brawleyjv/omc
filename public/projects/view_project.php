<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__ for relative path
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
$controller = new ProjectController($database);

if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    $project = $controller->getProjectById($project_id);
} else {
    echo "Project ID is missing.";
    exit();
}

// ...existing code for displaying project details...
?>
