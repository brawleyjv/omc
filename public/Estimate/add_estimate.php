<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Updated to use realpath
require_once BASE_PATH . 'Models/Database.php'; // Updated to match site structure
require_once BASE_PATH . 'Controllers/EstimateController.php'; // Updated to match site structure
require_once BASE_PATH . 'Models/EstimateModel.php'; // Updated to match site structure

use MyApp\Models\Database;
use MyApp\Controllers\EstimateController;

// Establish database connection
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD); // Use direct config values
$conn = $database->getConnection();

// Pass the required arguments to the EstimateController constructor
$projectId = 'project_id'; // Replace with actual value
$estimateData = 'estimate_data'; // Replace with actual value
$userId = 'user_id'; // Replace with actual value
$estimateController = new EstimateController($conn, $projectId, $estimateData, $userId); // Pass the correct arguments

$estimateModel = new EstimateModel($conn, $projectId, $estimateData, $userId); // Pass the correct arguments

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission to add estimate
    // Add your code here to process the form data and add the estimate
}

include realpath(dirname(__FILE__) . '/../../Views/header.php'); // Updated to use realpath
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Estimate</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>