<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Use correct config values
$projectsController = new ProjectController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_name'])) {
    $projectName = $_POST['delete_project_name'];
    error_log("Deleting project with name: $projectName"); // Log the project name being deleted
    $projectsController->deleteProjectByName($projectName);
    header('Location: ' . BASE_URL . 'public/projects/list_projects.php'); // Redirect to refresh the list after deletion
    exit;
}

$projects = $projectsController->listProjects(); // Use the correct method to list projects

// Pass the data to the HTML view
require_once BASE_PATH . '/Views/projects/list_projects.php';
?>