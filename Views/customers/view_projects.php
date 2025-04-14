<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Controllers/ProjectController.php';

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getConnection();
$projectController = new ProjectController($conn);

$customerId = $_GET['customer_id'] ?? null;

if (!$customerId) {
    die('Customer ID is required.');
}

$projects = $projectController->getProjectsByCustomerId($customerId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Projects</title>
</head>
<body>
    <h1>Projects for Customer ID: <?php echo htmlspecialchars($customerId); ?></h1>
    <ul>
        <?php foreach ($projects as $project): ?>
            <li>
                <strong>Project Name:</strong> <?php echo htmlspecialchars($project['project_name']); ?><br>
                <strong>Description:</strong> <?php echo htmlspecialchars($project['project_description']); ?><br>
                <strong>Due Date:</strong> <?php echo htmlspecialchars($project['due_date']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
