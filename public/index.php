<?php
require_once __DIR__ . '/../Globals/Config.php';
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;
use Globals\Config;

// Ensure Database is instantiated with required arguments
$database = new Database($host, $username, $password, $dbname);
$controller = new ProjectController($database);

$results = $controller->listProjects();
$controller->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
    <h1>Home</h1>
    <div id="results">
        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No projects found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
