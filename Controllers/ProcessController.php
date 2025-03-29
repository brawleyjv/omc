<?php
namespace MyApp\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;
use PDO;

class ProcessController {
    private $db;

    public function __construct() {
        // Ensure the Database class is instantiated with the required arguments
        $this->db = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Pass required arguments
    }

    public function listProjects() {
        $conn = $this->db->getConnection(); // Get the database connection

        if (!$conn) {
            throw new \Exception("Database connection is null.");
        }

        $query = "SELECT * FROM projects";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function closeConnection() {
        $this->db = null; // Close the database connection
    }
}
?>