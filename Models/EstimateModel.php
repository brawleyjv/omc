<?php
require_once realpath(dirname(__FILE__) . '/../config.php');

class EstimateModel {
    private $db;
    private $projectId;
    private $estimateData;
    private $userId;

    public function __construct($db, $projectId, $estimateData, $userId) {
        $this->db = $db;
        $this->projectId = $projectId;
        $this->estimateData = $estimateData;
        $this->userId = $userId;
    }

    public function saveEstimate($projectId, $routerTime, $laserTime, $laborTime, $customerName) {
        try {
            $query = "INSERT INTO estimates (project_id, router_time, laser_time, labor_time, customer_name) 
                      VALUES (:project_id, :router_time, :laser_time, :labor_time, :customer_name)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':router_time', $routerTime);
            $stmt->bindParam(':laser_time', $laserTime);
            $stmt->bindParam(':labor_time', $laborTime);
            $stmt->bindParam(':customer_name', $customerName);
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error saving estimate: " . $e->getMessage());
            throw new \Exception("Failed to save estimate.");
        }
    }
}
?>