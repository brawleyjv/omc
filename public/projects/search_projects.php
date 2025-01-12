<?php
// filepath: /c:/xampp/htdocs/OMC/public/projects/search_projects.php

require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';
use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

// Ensure Database is instantiated with required arguments
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$projectsController = new ProjectController($database);

$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

$results = [];
$noResults = false;
if (!empty($search_term)) {
    // Ensure searchProjects method exists in ProjectController
    $results = $projectsController->searchProjects($search_term);
    if (empty($results)) {
        $noResults = true;
    }
}

include __DIR__ . '/../../Views/projects/search_projects.php';
?>