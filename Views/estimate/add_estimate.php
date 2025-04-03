<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Corrected path to config.php
require_once BASE_PATH . 'Models/Database.php'; // Updated path
require_once BASE_PATH . 'Controllers/ProjectController.php'; // Updated path
require_once BASE_PATH . 'Models/Bom.php'; // Updated path

use MyApp\Models\Database;
use MyApp\Controllers\ProjectController;
use MyApp\Models\Bom;

// Establish database connection
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getConnection();
$projectController = new ProjectController($conn); // Pass the PDO connection
$bomModel = new Bom($conn); // Pass the PDO connection

$projects = [];
$selectedProject = null;
$bomMaterials = [];
$totalBomCost = 0; // Initialize $totalBomCost to 0

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_term'])) {
    $searchTerm = $_POST['search_term'];
    $projects = $projectController->searchProjects($searchTerm);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_name'])) {
    $customerName = $_POST['customer_name'];
    $projects = $projectController->getProjectsByCustomerName($customerName); // Fetch projects by customer name
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'])) {
    $projectId = $_POST['project_id'];
    $selectedProject = $projectController->getProjectById($projectId);
    $bomMaterials = $bomModel->getBomByProjectId($projectId);

    // Calculate total BOM cost
    foreach ($bomMaterials as $material) {
        $price = $material['price'] ?? 0; // Default to 0 if price is missing
        $totalBomCost += $material['quantity'] * $price;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Estimate</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css?v=<?php echo time(); ?>"> <!-- Updated to use BASE_URL -->
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
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated BASE_PATH -->
    <div class="container">
        <h1 class="title">Add Estimate</h1>
        <form action="<?php echo BASE_URL; ?>Views/estimate/add_estimate.php" method="post"> <!-- Updated BASE_URL -->
            <label for="customer_name">Search Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
            <input type="submit" value="Search" class="btn styled-btn">
        </form>
        <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/estimate/estimate.php'">Cancel</button> <!-- Added Cancel button -->
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
                <?php if (!empty($bomMaterials)): ?> <!-- Check if BOM materials are present -->
                    <ul>
                        <?php foreach ($bomMaterials as $material): ?>
                            <li>
                                <p><strong>Material ID:</strong> <?php echo htmlspecialchars($material['id']); ?></p>
                                <p><strong>Material Name:</strong> <?php echo htmlspecialchars($material['material_name']); ?></p>
                                <p><strong>Quantity:</strong> <?php echo htmlspecialchars($material['quantity']); ?></p>
                                <p><strong>Price:</strong> $<?php echo htmlspecialchars($material['price'] ?? '0'); ?></p> <!-- Use 'price' instead of 'unit_cost' -->
                                <p><strong>Total Cost:</strong> $<?php echo htmlspecialchars(($material['quantity'] * ($material['price'] ?? 0))); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>Total BOM Cost:</strong> $<?php echo number_format($totalBomCost, 2); ?></p>
                    <!-- Update the Edit BOM button -->
                    <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/bom/add_bom.php?project_id=<?php echo urlencode($selectedProject['project_id']); ?>&project_name=<?php echo urlencode($selectedProject['project_name']); ?>&customer_id=<?php echo urlencode($selectedProject['customer_id']); ?>'">Edit BOM</button>
                <?php else: ?>
                    <p>No BOM found for this project.</p>
                    <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/bom/add_bom.php?project_id=<?php echo urlencode($selectedProject['id']); ?>&project_name=<?php echo urlencode($selectedProject['project_name']); ?>&customer_name=<?php echo urlencode($selectedProject['customer_name']); ?>'">Add BOM</button> <!-- Updated to include project_id -->
                <?php endif; ?>
            </div>
            <form action="<?php echo BASE_URL; ?>Views/estimate/add_estimate.php" method="post"> <!-- Updated BASE_URL -->
                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($selectedProject['project_id']); ?>"> <!-- Use 'project_id' -->
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
            <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/estimate/estimate.php'">Cancel</button> <!-- Added Cancel button -->
        <?php endif; ?>
        <form id="select-project-form" action="<?php echo BASE_URL; ?>Views/estimate/add_estimate.php" method="post" style="display: none;"> <!-- Updated BASE_URL -->
            <input type="hidden" id="id" name="project_id"> <!-- Use 'project_id' -->
            <input type="hidden" id="project_name" name="project_name">
            <input type="hidden" id="project_description" name="project_description">
            <input type="hidden" id="router_time" name="router_time">
            <input type="hidden" id="laser_time" name="laser_time">
        </form>
    </div>
    <script>
        function selectProject(project) {
            // Ensure the form fields are populated with the selected project details
            document.getElementById('id').value = project.project_id; // Use 'project_id'
            document.getElementById('project_name').value = project.project_name;
            document.getElementById('project_description').value = project.project_description;
            document.getElementById('router_time').value = project.router_time;
            document.getElementById('laser_time').value = project.laser_time;

            // Submit the form to load the selected project
            document.getElementById('select-project-form').submit();
        }
    </script>
</body>
</html>
