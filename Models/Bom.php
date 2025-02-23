<?php

namespace MyApp\Models;

use PDO;

class Bom {
    private $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    public function addBom($project_name, $material_name, $length, $width, $thickness, $quantity) {
        $query = "INSERT INTO bom (project_name, material_name, length, width, thickness, quantity) 
                  VALUES (:project_name, :material_name, :length, :width, :thickness, :quantity)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->bindValue(':material_name', $material_name, PDO::PARAM_STR);
        $stmt->bindValue(':length', $length, PDO::PARAM_STR);
        $stmt->bindValue(':width', $width, PDO::PARAM_STR);
        $stmt->bindValue(':thickness', $thickness, PDO::PARAM_STR);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getBomByProjectName($project_name) {
        $query = "SELECT * FROM bom WHERE project_name = :project_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectAndCustomerDetails($project_name) {
        $query = "SELECT p.project_name, p.customer_name FROM projects p WHERE p.project_name = :project_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':project_name', $project_name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
