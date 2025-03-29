<?php
namespace MyApp\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

class EstimateController {
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

    // Add your methods here
}
?>
