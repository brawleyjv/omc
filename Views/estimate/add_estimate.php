<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';
require_once __DIR__ . '/../../Controllers/ProjectController.php';
require_once __DIR__ . '/../../Models/Bom.php';

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;
use MyApp\Models\Bom;
use Globals\Config;

// Establish database connection
$database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
$conn = $database->getConnection();
$projectController = new ProjectController($database);
$bomModel = new Bom($database);

$projects = [];
$selectedProject = null;
$bomMaterials = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
    $projects = $projectController->searchProjects($searchTerm);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'])) {
    $projectId = $_POST['project_id'];
    $selectedProject = $projectController->getProjectById($projectId);
    $bomMaterials = $bomModel->getBomByProjectId($projectId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Estimate</title>
    <link rel="stylesheet" href="/OMC/public/css/styles.css?v=<?php echo time(); ?>"> <!-- Add version query string -->
    <style>
        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center the text */
            width: 80%;
            max-width: 1024px;
            margin-top: 20px; /* Adjust the top margin to ensure the container is visible */
            padding: 20px; /* Add padding to the container */
        }
        .title {
            text-align: center;
            margin-top: 20px; /* Adjust the top margin for the title */
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            margin-bottom: 10px;
            font-size: 18px;
        }
        input[type="text"], input[type="number"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            max-width: 300px;
        }
        input[type="submit"], .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        input[type="submit"]:hover, .btn:hover {
            background-color: #0056b3;
        }
        .project-list {
            list-style-type: none;
            padding: 0;
        }
        .project-list li {
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .project-list li:hover {
            background-color: #f0f0f0;
        }
        .project-details {
            text-align: left;
            margin-top: 20px;
        }
        .project-details label {
            font-weight: bold;
        }
        .bom-details {
            text-align: left;
            margin-top: 20px;
        }
        .bom-details label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Add Estimate</h1>
        <form action="add_estimate.php" method="post">
            <label for="search_term">Search Project Name:</label>
            <input type="text" id="search_term" name="search_term" required>
            <input type="submit" value="Search" class="btn styled-btn">
        </form>
        <button class="btn styled-btn" onclick="window.location.href='add_estimate.php'">Start New Estimate</button> <!-- Start New Estimate button -->
        <?php if (!empty($projects)): ?>
            <h2>Select a Project</h2>
            <ul class="project-list">
                <?php foreach ($projects as $project): ?>
                    <li onclick="selectProject(<?php echo htmlspecialchars(json_encode($project)); ?>)">
                        <?php echo htmlspecialchars($project['project_name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($selectedProject): ?>
            <div class="project-details">
                <h2>Project Details</h2>
                <p><label>Project Name:</label> <?php echo htmlspecialchars($selectedProject['project_name'] ?? 'Not Available'); ?></p>
                <p><label>Project Description:</label> <?php echo htmlspecialchars($selectedProject['project_description'] ?? 'Not Available'); ?></p>
                <p><label>Router Time:</label> <?php echo htmlspecialchars($selectedProject['router_time'] ?? 'Not Available'); ?></p>
                <p><label>Laser Time:</label> <?php echo htmlspecialchars($selectedProject['laser_time'] ?? 'Not Available'); ?></p>
            </div>
            <div class="bom-details">
                <h2>BOM Details</h2>
                <?php if (!empty($bomMaterials)): ?>
                    <ul>
                        <?php foreach ($bomMaterials as $material): ?>
                            <li>
                                <p><label>Material Name:</label> <?php echo htmlspecialchars($material['material_name']); ?></p>
                                <p><label>Length:</label> <?php echo htmlspecialchars($material['length']); ?></p>
                                <p><label>Width:</label> <?php echo htmlspecialchars($material['width']); ?></p>
                                <p><label>Thickness:</label> <?php echo htmlspecialchars($material['thickness']); ?></p>
                                <p><label>Quantity:</label> <?php echo htmlspecialchars($material['quantity']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No BOM found for this project.</p>
                <?php endif; ?>
            </div>
            <form action="add_estimate.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($selectedProject['id']); ?>">
                <label for="project_name">Project Name:</label>
                <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($selectedProject['project_name'] ?? ''); ?>" required>
                <label for="project_description">Project Description:</label>
                <input type="text" id="project_description" name="project_description" value="<?php echo htmlspecialchars($selectedProject['project_description'] ?? ''); ?>" required>
                <label for="router_time">Router Time:</label>
                <input type="number" id="router_time" name="router_time" value="<?php echo htmlspecialchars($selectedProject['router_time'] ?? ''); ?>" required>
                <label for="laser_time">Laser Time:</label>
                <input type="number" id="laser_time" name="laser_time" value="<?php echo htmlspecialchars($selectedProject['laser_time'] ?? ''); ?>" required>
                <input type="submit" value="Start Estimate" class="btn styled-btn">
            </form>
        <?php endif; ?>
        <form id="select-project-form" action="add_estimate.php" method="post" style="display: none;">
            <input type="hidden" id="project_id" name="project_id">
            <input type="hidden" id="project_name" name="project_name">
            <input type="hidden" id="project_description" name="project_description">
            <input type="hidden" id="router_time" name="router_time">
            <input type="hidden" id="laser_time" name="laser_time">
        </form>
    </div>
    <script>
        function selectProject(project) {
            document.getElementById('project_id').value = project.id;
            document.getElementById('project_name').value = project.project_name;
            document.getElementById('project_description').value = project.project_description;
            document.getElementById('router_time').value = project.router_time;
            document.getElementById('laser_time').value = project.laser_time;
            document.getElementById('select-project-form').submit();
        }
    </script>
</body>
</html>
