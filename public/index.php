<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Adjusted path for config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

use MyApp\Controllers\ProjectController;
use MyApp\Models\Database;

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS); // Removed Globals\Config

// Access the connection using the public method
$conn = $database->getConnection(); // Use getConnection instead of connect

// Example usage of getter methods if needed
// Removed $host = $database->getHost(); as getHost is undefined

// Removed $user = $database->getUser(); as getUser is undefined

$controller = new ProjectController($database);

$results = $controller->listProjects();
// $controller->closeConnection(); // Removed as it is not needed

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Adjusted path -->
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
