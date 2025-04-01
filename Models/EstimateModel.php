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

    // ...existing code...
}
?>