<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include config.php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Controllers/EstimateController.php';
require_once BASE_PATH . 'Models/EstimateModel.php';
require_once BASE_PATH . 'Controllers/MaterialController.php';

use MyApp\Models\Database;
use MyApp\Controllers\EstimateController;
use MyApp\Models\EstimateModel;
use MyApp\Controllers\MaterialController;

// Initialize variables
$matchingProjects = [];
$projectDetails = null;
$errorMessage = null;
$bomMaterials = [];
$setupRates = [];
$materials = [];

try {
    // Establish database connection
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getPdo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['project_search'])) {
            // Search for projects by name or description
            $searchTerm = $_POST['project_search'];
            $query = "SELECT id, project_name, description FROM projects 
                      WHERE project_name LIKE :search_term OR description LIKE :search_term";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':search_term', '%' . $searchTerm . '%', PDO::PARAM_STR);
            $stmt->execute();
            $matchingProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif (!empty($_POST['select_project'])) {
            // Fetch selected project details
            $projectId = $_POST['select_project'];
            $query = "SELECT * FROM projects WHERE id = :project_id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            $projectDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$projectDetails) {
                $errorMessage = "Project not found.";
            } else {
                // Fetch BOM details for the project
                $bomQuery = "SELECT * FROM bom WHERE project_id = :project_id";
                $bomStmt = $conn->prepare($bomQuery);
                $bomStmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
                $bomStmt->execute();
                $bomMaterials = $bomStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch rates from the setup table
                $setupQuery = "SELECT mill_rate, laser_rate, labor_rate, bit_change_rate, customize_rate FROM setup LIMIT 1";
                $setupStmt = $conn->prepare($setupQuery);
                $setupStmt->execute();
                $setupRates = $setupStmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }

    // Handle material search
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['material_search'])) {
        $materialSearch = $_POST['material_search'];
        $materialController = new MaterialController($database);
        $materials = $materialController->searchMaterials($materialSearch);
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $errorMessage = "An error occurred while processing your request.";
}

// Include the view
include BASE_PATH . 'Views/estimate/create_estimate.php';
