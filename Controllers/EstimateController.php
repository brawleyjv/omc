<?php
namespace MyApp\Controllers;

use PDO; // Import the PDO class
require_once realpath(dirname(__FILE__) . '/../config.php');
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

class EstimateController {
    private PDO $db; // Ensure type is PDO
    private int $projectId;
    private array $estimateData;
    private int $userId;

    public function __construct(PDO $db, int $projectId, array $estimateData, int $userId) {
        $this->db = $db;
        $this->projectId = $projectId;
        $this->estimateData = $estimateData;
        $this->userId = $userId;
    }

    public function saveEstimate(int $projectId, float $bomCost, float $laborCost, array $additionalCosts, float $totalCost): void {
        try {
            // Insert the estimate into the `estimate` table
            $query = "INSERT INTO estimate (project_id, bom_cost, labor_cost, additional_costs, total_cost) 
                      VALUES (:project_id, :bom_cost, :labor_cost, :additional_costs, :total_cost)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->bindValue(':bom_cost', $bomCost, PDO::PARAM_STR);
            $stmt->bindValue(':labor_cost', $laborCost, PDO::PARAM_STR);
            $stmt->bindValue(':additional_costs', json_encode($additionalCosts), PDO::PARAM_STR); // Serialize additional costs as JSON
            $stmt->bindValue(':total_cost', $totalCost, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) { // Simplified from \PDOException
            throw new \Exception("Failed to save estimate: " . $e->getMessage());
        }
    }

    public function createEstimate($projectId, $bomCost, $laborCost, $additionalCosts) {
        $totalCost = $bomCost + $laborCost + array_sum(array_column($additionalCosts, 'amount'));

        $this->saveEstimate($projectId, $bomCost, $laborCost, $additionalCosts, $totalCost);
    }

    // Add your methods here
}
?>
