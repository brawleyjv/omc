<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php

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