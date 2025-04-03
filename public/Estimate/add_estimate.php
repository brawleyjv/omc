<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Updated to use realpath
require_once BASE_PATH . 'Models/Database.php'; // Updated to match site structure
require_once BASE_PATH . 'Controllers/EstimateController.php'; // Updated to match site structure
require_once BASE_PATH . 'Models/EstimateModel.php'; // Updated to match site structure
require_once BASE_PATH . 'Models/Bom.php'; // Include BOM model
require_once BASE_PATH . 'Controllers/ProjectController.php'; // Include ProjectController

use MyApp\Models\Database;
use MyApp\Controllers\EstimateController;
use MyApp\Models\Bom;
use MyApp\Controllers\ProjectController;

// Establish database connection
$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn = $database->getConnection();

// Initialize controllers and models
$projectId = $_GET['project_id'] ?? null; // Retrieve project ID from the query parameter
$estimateData = isset($_POST['estimate_data']) ? json_decode($_POST['estimate_data'], true) : []; // Decode JSON string to array
if (!is_array($estimateData)) {
    $estimateData = []; // Ensure it defaults to an empty array if decoding fails
}
$userId = $_SESSION['user_id'] ?? null; // Assuming user ID is stored in the session

if (!$projectId || !$userId) {
    die('Project ID and User ID are required.');
}

$estimateController = new EstimateController($conn, $projectId, $estimateData, $userId); // Pass all required arguments
$projectController = new ProjectController($conn); // Use ProjectController for project-related operations
$bomModel = new Bom($conn);

// Fetch project and BOM details
$selectedProject = null;
$bomMaterials = [];
$totalBomCost = 0;

if ($projectId) {
    $selectedProject = $projectController->getProjectById($projectId); // Use ProjectController to fetch project details
    $bomMaterials = $bomModel->getBomByProjectId($projectId); // Fetch BOM materials

    // Calculate total BOM cost
    foreach ($bomMaterials as $material) {
        $totalBomCost += $material['quantity'] * $material['unit_cost']; // Assuming `unit_cost` is available
    }
}

// Handle form submission to save the estimate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $laborCost = $_POST['labor_cost'] ?? 0;
    $additionalCosts = $_POST['additional_costs'] ?? [];
    $totalCost = $totalBomCost + $laborCost;

    foreach ($additionalCosts as $cost) {
        $totalCost += $cost['amount'];
    }

    $estimateController->saveEstimate($projectId, $totalBomCost, $laborCost, $additionalCosts, $totalCost);
    header("Location: " . BASE_URL . "Views/estimate/estimate.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Estimate</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Add Estimate</h1>
        <?php if ($selectedProject): ?>
            <div class="project-details">
                <h2>Project Details</h2>
                <p><strong>Project Name:</strong> <?php echo htmlspecialchars($selectedProject['project_name']); ?></p>
                <p><strong>Project Description:</strong> <?php echo htmlspecialchars($selectedProject['project_description']); ?></p>
            </div>
            <div class="bom-details">
                <h2>BOM Details</h2>
                <?php if (!empty($bomMaterials)): ?>
                    <ul>
                        <?php foreach ($bomMaterials as $material): ?>
                            <li>
                                <p><strong>Material Name:</strong> <?php echo htmlspecialchars($material['material_name']); ?></p>
                                <p><strong>Quantity:</strong> <?php echo htmlspecialchars($material['quantity']); ?></p>
                                <p><strong>Unit Cost:</strong> $<?php echo htmlspecialchars($material['unit_cost']); ?></p>
                                <p><strong>Total Cost:</strong> $<?php echo htmlspecialchars($material['quantity'] * $material['unit_cost']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>Total BOM Cost:</strong> $<?php echo number_format($totalBomCost, 2); ?></p>
                    <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/bom/add_bom.php?project_id=<?php echo urlencode($selectedProject['id']); ?>&project_name=<?php echo urlencode($selectedProject['project_name']); ?>&customer_name=<?php echo urlencode($selectedProject['customer_name']); ?>'">Edit BOM</button> <!-- Added Edit BOM button -->
                <?php else: ?>
                    <p>No BOM found for this project.</p>
                    <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/bom/add_bom.php?project_id=<?php echo urlencode($selectedProject['id']); ?>&project_name=<?php echo urlencode($selectedProject['project_name']); ?>&customer_name=<?php echo urlencode($selectedProject['customer_name']); ?>'">Add BOM</button>
                <?php endif; ?>
            </div>
            <form action="" method="post">
                <h2>Additional Costs</h2>
                <div id="additional-costs-container">
                    <div class="additional-cost">
                        <label for="description_0">Description:</label>
                        <input type="text" name="additional_costs[0][description]" id="description_0" required>
                        <label for="amount_0">Amount:</label>
                        <input type="number" step="0.01" name="additional_costs[0][amount]" id="amount_0" required>
                    </div>
                </div>
                <button type="button" onclick="addAdditionalCost()">Add Additional Cost</button>
                <h2>Labor Cost</h2>
                <label for="labor_cost">Labor Cost:</label>
                <input type="number" step="0.01" name="labor_cost" id="labor_cost" required>
                <h2>Total Estimate</h2>
                <p><strong>Total Cost:</strong> $<?php echo number_format($totalBomCost, 2); ?> + Additional Costs</p>
                <button type="submit">Save Estimate</button>
            </form>
        <?php else: ?>
            <p>No project selected. Please go back and select a project.</p>
        <?php endif; ?>
    </div>
    <script>
        function addAdditionalCost() {
            const container = document.getElementById('additional-costs-container');
            const index = container.children.length;
            const div = document.createElement('div');
            div.classList.add('additional-cost');
            div.innerHTML = `
                <label for="description_${index}">Description:</label>
                <input type="text" name="additional_costs[${index}][description]" id="description_${index}" required>
                <label for="amount_${index}">Amount:</label>
                <input type="number" step="0.01" name="additional_costs[${index}][amount]" id="amount_${index}" required>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>