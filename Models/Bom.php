<?php

namespace MyApp\Models;

require_once realpath(dirname(__FILE__) . '/../config.php');

use PDO;

class Bom {
    private PDO $conn;

    public function __construct(PDO $conn) { // Ensure type is PDO
        $this->conn = $conn;
    }

    public function addBom($project_id, $material_name, $length, $width, $thickness, $quantity) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        $query = "INSERT INTO bom (project_id, material_name, length, width, thickness, quantity) 
                  VALUES (:project_id, :material_name, :length, :width, :thickness, :quantity)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindValue(':material_name', $material_name, PDO::PARAM_STR);
        $stmt->bindValue(':length', $length, PDO::PARAM_STR);
        $stmt->bindValue(':width', $width, PDO::PARAM_STR);
        $stmt->bindValue(':thickness', $thickness, PDO::PARAM_STR);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getBomByProjectName($project_name) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        $query = "SELECT * FROM bom WHERE project_name = :project_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectAndCustomerDetails($project_name) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        $query = "SELECT p.project_name, p.customer_name FROM projects p WHERE p.project_name = :project_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBomByProjectId($project_id) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        $query = "SELECT b.*, m.price 
                  FROM bom b 
                  JOIN materials m ON b.material_name = m.material_name 
                  WHERE b.project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
