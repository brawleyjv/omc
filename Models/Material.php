<?php
// filepath: /c:/xampp/htdocs/OMC/Models/Material.php

namespace MyApp\Models;

use PDO;

class Material {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database->getConnection();
    }

    public static function getById($conn, $material_id) {
        $stmt = $conn->prepare("SELECT * FROM materials WHERE id = :id");
        $stmt->bindParam(':id', $material_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($conn, $material_id, $material_name, $length, $width, $thickness, $price, $quantity_on_hand, $type, $vendor, $item_no, $item_url, $image_url) {
        $sql = "UPDATE materials SET 
                material_name = :material_name, 
                Length = :length, 
                Width = :width, 
                Thickness = :thickness, 
                Price = :price, 
                Quantity_on_Hand = :quantity_on_hand, 
                type = :type, 
                vendor = :vendor, 
                Item_no = :item_no, 
                item_url = :item_url, 
                image_url = :image_url 
                WHERE id = :material_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':material_name', $material_name);
        $stmt->bindParam(':length', $length);
        $stmt->bindParam(':width', $width);
        $stmt->bindParam(':thickness', $thickness);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':quantity_on_hand', $quantity_on_hand);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':vendor', $vendor);
        $stmt->bindParam(':item_no', $item_no);
        $stmt->bindParam(':item_url', $item_url);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':material_id', $material_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function search($conn, $search_term) {
        $stmt = $conn->prepare("SELECT * FROM materials WHERE material_name LIKE :search_term OR type LIKE :search_term OR vendor LIKE :search_term OR Item_no LIKE :search_term");
        $search_term = "%$search_term%";
        $stmt->bindParam(':search_term', $search_term, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function submit($conn, $data) {
        $sql = "INSERT INTO materials (material_name, Length, Width, Thickness, Price, Quantity_on_Hand, type, vendor, Item_no, item_url, image_url) 
                VALUES (:material_name, :length, :width, :thickness, :price, :quantity_on_hand, :type, :vendor, :item_no, :item_url, :image_url)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':material_name', $data['material_name']);
        $stmt->bindParam(':length', $data['length']);
        $stmt->bindParam(':width', $data['width']);
        $stmt->bindParam(':thickness', $data['thickness']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':quantity_on_hand', $data['quantity_on_hand']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':vendor', $data['vendor']);
        $stmt->bindParam(':item_no', $data['item_no']);
        $stmt->bindParam(':item_url', $data['item_url']);
        $stmt->bindParam(':image_url', $data['image_url']);

        return $stmt->execute();
    }

    public static function getAll($conn) {
        $stmt = $conn->prepare("SELECT * FROM materials");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fetchAll(Database $database) {
        $conn = $database->getConnection();
        if ($conn === null) {
            throw new \Exception("Database connection failed.");
        }
        $query = "SELECT materials.id, materials.material_name, materials.Length, materials.Width, materials.Thickness, materials.Price, materials.Quantity_on_Hand, materials.type, vendors.vendor AS vendor_name, materials.Item_no, materials.item_url, materials.image_url 
                  FROM materials 
                  LEFT JOIN vendors ON materials.vendor = vendors.id";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPriceById($material_id) {
        $stmt = $this->db->prepare("SELECT price FROM materials WHERE id = ?");
        $stmt->execute([$material_id]);
        $result = $stmt->fetch();
        return $result ? $result['price'] : null;
    }
}
?>