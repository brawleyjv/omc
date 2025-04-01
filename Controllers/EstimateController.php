<?php
namespace MyApp\Controllers;

include realpath(dirname(__FILE__) . '/../config.php');require_once BASE_PATH . '/Models/Database.php';

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
