<?php
namespace MyApp\Controllers;

include realpath(dirname(__FILE__) . '/../config.php');require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;
use PDO;

class EstimateController {
    private $db;
    private $projectId;
    private $estimateData;
    private $userId;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function addEstimate($projectId, $estimateData, $userId) {
        try {
            $query = "INSERT INTO estimates (project_id, estimate_data, user_id) VALUES (:project_id, :estimate_data, :user_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':estimate_data', $estimateData);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error adding estimate: " . $e->getMessage());
            throw new \Exception("Failed to add estimate.");
        }
    }

    public function searchProjects($searchTerm) {
        try {
            // Log the search term for debugging
            error_log("EstimateController: Search Term: " . $searchTerm);

            // Perform a case-insensitive search
            $query = "SELECT id, project_name, project_description FROM projects 
                      WHERE LOWER(project_name) LIKE LOWER(:search_term) 
                         OR LOWER(project_description) LIKE LOWER(:search_term)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':search_term', '%' . $searchTerm . '%', PDO::PARAM_STR);

            // Log the query and bind parameters for debugging
            error_log("EstimateController: Query: " . $query);
            error_log("EstimateController: Bind Parameter: " . '%' . $searchTerm . '%');

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log the results for debugging
            if (!empty($results)) {
                error_log("EstimateController: Results Found: " . json_encode($results));
            } else {
                error_log("EstimateController: No Results Found");
            }

            return $results;
        } catch (\PDOException $e) {
            error_log("Error searching projects: " . $e->getMessage());
            throw new \Exception("Failed to search projects.");
        }
    }
}
?>
