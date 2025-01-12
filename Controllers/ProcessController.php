<?php
// filepath: /c:/xampp/htdocs/OMC/Controllers/ProcessController.php

namespace MyApp\Controllers;

use MyApp\Models\Database;
use Globals\Config;
use PDO;

class ProcessController {
    private $db;

    public function __construct() {
        $this->db = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    }

    public function listProjects() {
        $conn = $this->db->getConnection();

        $query = "SELECT * FROM projects";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function closeConnection() {
        $this->db = null;
    }
}