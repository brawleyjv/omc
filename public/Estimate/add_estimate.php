<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include config.php only once
if (!defined('BASE_PATH')) {
    require_once realpath(dirname(__FILE__) . '/../../config.php');
}

require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Controllers/EstimateController.php';

use MyApp\Models\Database;
use MyApp\Controllers\EstimateController;

// Initialize variables
$matchingProjects = [];
$errorMessage = null;

try {
    // Establish database connection using credentials from config.php
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo(); // Use getPdo() to get the PDO instance
    $estimateController = new EstimateController($conn); // Initialize EstimateController

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['project_search'])) {
            // Search for projects by name or description using EstimateController
            $searchTerm = trim($_POST['project_search']);
            if (!empty($searchTerm)) {
                // Log the search term for debugging
                error_log("Search Term: " . $searchTerm);

                // Perform the search
                $matchingProjects = $estimateController->searchProjects($searchTerm);

                // Log the results for debugging
                if (!empty($matchingProjects)) {
                    error_log("Matching Projects Found: " . json_encode($matchingProjects));
                } else {
                    error_log("No Matching Projects Found");
                }

                if (empty($matchingProjects)) {
                    $errorMessage = "No projects found matching your search criteria.";
                }
            } else {
                $errorMessage = "Please enter a valid search term.";
            }
        } elseif (!empty($_POST['select_project'])) {
            // Redirect to create_estimate.php with the selected project ID
            $projectId = $_POST['select_project'];
            header("Location: " . BASE_URL . "public/Estimate/create_estimate.php?project_id=" . urlencode($projectId));
            exit();
        }
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $errorMessage = "An error occurred while processing your request.";
}

// Include the HTML view
include BASE_PATH . 'Views/estimate/add_estimate.php';