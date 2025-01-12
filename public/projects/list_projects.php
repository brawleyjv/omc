<?php
error_log("Test log entry: list_projects.php loaded");

// filepath: /c:/xampp/htdocs/OMC/Views/projects/list_projects.php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Models/Project.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

// Ensure Database is instantiated with required arguments
$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$projectsController = new ProjectController($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project_id'])) {
    $projectId = $_POST['delete_project_id'];
    error_log("Deleting project with ID: $projectId"); // Log the project ID being deleted
    $projectsController->deleteProject($projectId);
    header('Location: ../../public/projects/list_projects.php'); // Redirect to refresh the list after deletion
    exit;
}

$projects = $projectsController->listProjects(); // Use the correct method to list projects
?>