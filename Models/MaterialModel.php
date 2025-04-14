<?php
namespace MyApp\Models;

require_once realpath(dirname(__FILE__) . '/../config.php');

use PDO;

class MaterialModel {
    private $connection;

    public function __construct(Database $database) { // Updated to accept a Database instance
        $this->connection = $database->getConnection(); // Store the database connection

        if (!$this->connection) {
            throw new \Exception("Database connection is null.");
        }
    }

    public function createMaterial($data) {
        if (!$this->connection) {
            throw new \Exception("Database connection is null.");
        }
        $stmt = $this->connection->prepare('
            INSERT INTO materials (name, description, quantity, price) 
            VALUES (:name, :description, :quantity, :price)
        ');
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'price' => $data['price']
        ]);
    }

    public function getAllMaterials() {
        if (!$this->connection) {
            throw new \Exception("Database connection is null.");
        }
        $stmt = $this->connection->query('SELECT * FROM materials');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMaterialById($materialId) {
        if (!$this->connection) {
            throw new \Exception("Database connection is null.");
        }
        $stmt = $this->connection->prepare('SELECT * FROM materials WHERE id = :id');
        $stmt->execute(['id' => $materialId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateMaterial($materialId, $data) {
        if (!$this->connection) {
            throw new \Exception("Database connection is null.");
        }
        $stmt = $this->connection->prepare('
            UPDATE materials 
            SET 
                name = :name, 
                description = :description, 
                quantity = :quantity, 
                price = :price 
            WHERE id = :id
        ');
        return $stmt->execute([
            'id' => $materialId,
            'name' => $data['name'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'price' => $data['price']
        ]);
    }

    public function deleteMaterial($materialId) {
        if (!$this->connection) {
            throw new \Exception("Database connection is null.");
        }
        $stmt = $this->connection->prepare('DELETE FROM materials WHERE id = :id');
        return $stmt->execute(['id' => $materialId]);
    }

    public function deleteMaterialById($id) {
        $connection = $this->connection;
        $query = "DELETE FROM materials WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $result = $stmt->execute();
        unset($stmt);
        return $result;
    }
}
?>