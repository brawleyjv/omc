<?php

namespace MyApp\Models;

class Bom {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database->getConnection();
    }

    public function addBom($project_id, $material_name, $length, $width, $thickness, $quantity) {
        $stmt = $this->db->prepare("INSERT INTO bom (project_id, material_name, length, width, thickness, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $material_name, $length, $width, $thickness, $quantity]);
    }

    public function getBomByProjectId($project_id) {
        $stmt = $this->db->prepare("SELECT * FROM bom WHERE project_id = ?");
        $stmt->execute([$project_id]);
        return $stmt->fetchAll();
    }
}
?>
