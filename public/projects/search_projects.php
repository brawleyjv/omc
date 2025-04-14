<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

// Instantiate the Database class with required arguments
$projectController = new ProjectController($database);

$searchTerm = $_GET['search_term'] ?? '';

$projects = [];
if (!empty($searchTerm)) {
    $projects = $projectController->searchProjects($searchTerm);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Projects</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <h1>Search Projects</h1>
    <form action="<?php echo BASE_URL; ?>public/projects/search_projects.php" method="get">
        <input type="text" name="search_term" placeholder="Search for a project" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit">Search</button>
        <button type="button" onclick="window.location.href='<?php echo BASE_URL; ?>Views/projects/index.php';">Cancel</button>
    </form>
    <?php if (!empty($projects)): ?>
        <ul>
            <?php foreach ($projects as $project): ?>
                <li><?php echo htmlspecialchars($project['project_name']); ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No projects found.</li>
        <?php endif; ?>
    </ul>
</body>
</html>
