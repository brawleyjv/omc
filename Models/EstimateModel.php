<?php
require_once realpath(dirname(__FILE__) . '/../config.php');

class EstimateModel {
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

    // ...existing code...
}
?>