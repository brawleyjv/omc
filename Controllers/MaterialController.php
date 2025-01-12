<?php
// filepath: /c:/xampp/htdocs/OMC/Controllers/MaterialController.php

namespace MyApp\Controllers;

use MyApp\Models\Database;
use PDO;
use MyApp\Models\Material;

class MaterialController {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    public function getMaterialById($id) {
        $conn = $this->db->getConnection();

        $query = "SELECT materials.*, vendors.vendor AS vendor_name FROM materials 
                  LEFT JOIN vendors ON materials.vendor = vendors.id
                  WHERE materials.id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateMaterial($id, $material_name, $length, $width, $thickness, $price, $quantity_on_hand, $type, $vendor, $item_no, $item_url, $image_url) {
        $conn = $this->db->getConnection();

        $query = "UPDATE materials SET material_name = :material_name, Length = :length, Width = :width, Thickness = :thickness, Price = :price, Quantity_on_Hand = :quantity_on_hand, type = :type, vendor = :vendor, Item_no = :item_no, item_url = :item_url, image_url = :image_url WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':material_name', $material_name);
        $stmt->bindValue(':length', $length);
        $stmt->bindValue(':width', $width);
        $stmt->bindValue(':thickness', $thickness);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':quantity_on_hand', $quantity_on_hand);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':vendor', $vendor);
        $stmt->bindValue(':item_no', $item_no);
        $stmt->bindValue(':item_url', $item_url);
        $stmt->bindValue(':image_url', $image_url);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Failed to execute updateMaterial query: " . implode(", ", $stmt->errorInfo()));
        }
        return $result;
    }

    public function submitMaterial($material_name, $length, $width, $thickness, $price, $quantity_on_hand, $type, $vendor, $item_no, $item_url, $image_url) {
        $conn = $this->db->getConnection();

        // Check if material name already exists and append suffix if it does
        $material_name = $this->getUniqueMaterialName($conn, $material_name);

        $query = "INSERT INTO materials (material_name, Length, Width, Thickness, Price, Quantity_on_Hand, type, vendor, Item_no, item_url, image_url) VALUES (:material_name, :length, :width, :thickness, :price, :quantity_on_hand, :type, :vendor, :item_no, :item_url, :image_url)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':material_name', $material_name);
        $stmt->bindValue(':length', $length);
        $stmt->bindValue(':width', $width);
        $stmt->bindValue(':thickness', $thickness);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':quantity_on_hand', $quantity_on_hand);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':vendor', $vendor);
        $stmt->bindValue(':item_no', $item_no);
        $stmt->bindValue(':item_url', $item_url);
        $stmt->bindValue(':image_url', $image_url);
        $stmt->execute();
    }

    private function getUniqueMaterialName($conn, $material_name) {
        $original_name = $material_name;
        $suffix = 1;

        while ($this->materialNameExists($conn, $material_name)) {
            $material_name = $original_name . '-' . $suffix;
            $suffix++;
        }

        return $material_name;
    }

    private function materialNameExists($conn, $material_name) {
        $query = "SELECT COUNT(*) FROM materials WHERE material_name = :material_name";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':material_name', $material_name);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function searchMaterial($search_term) {
        $conn = $this->db->getConnection();

        $query = "SELECT materials.*, vendors.vendor AS vendor_name FROM materials 
                  LEFT JOIN vendors ON materials.vendor = vendors.id
                  WHERE 
                    LOWER(material_name) LIKE LOWER(:search_term) OR
                    LOWER(Length) LIKE LOWER(:search_term) OR
                    LOWER(Width) LIKE LOWER(:search_term) OR
                    LOWER(Thickness) LIKE LOWER(:search_term) OR
                    LOWER(Price) LIKE LOWER(:search_term) OR
                    LOWER(Quantity_on_Hand) LIKE LOWER(:search_term) OR
                    LOWER(type) LIKE LOWER(:search_term) OR
                    LOWER(vendors.vendor) LIKE LOWER(:search_term) OR
                    LOWER(Item_no) LIKE LOWER(:search_term) OR
                    LOWER(item_url) LIKE LOWER(:search_term) OR
                    LOWER(image_url) LIKE LOWER(:search_term)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':search_term', '%' . $search_term . '%', PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllMaterials() {
        $conn = $this->db->getConnection();

        $query = "SELECT materials.*, vendors.vendor AS vendor_name FROM materials 
                  LEFT JOIN vendors ON materials.vendor = vendors.id";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteMaterial($id) {
        $conn = $this->db->getConnection();

        $query = "DELETE FROM materials WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getDistinctTypes() {
        $conn = $this->db->getConnection();

        $query = "SELECT DISTINCT type_name FROM material_types";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistinctVendors() {
        $conn = $this->db->getConnection();

        $query = "SELECT DISTINCT vendor FROM materials";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllVendors() {
        $conn = $this->db->getConnection();

        $query = "SELECT id, vendor FROM vendors";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchMaterials($searchTerm) {
        $conn = $this->db->getConnection();
        $query = "SELECT materials.*, vendors.vendor AS vendor_name FROM materials 
                  LEFT JOIN vendors ON materials.vendor = vendors.id 
                  WHERE material_name LIKE :search_term 
                  OR type LIKE :search_term 
                  OR vendors.vendor LIKE :search_term";
        $stmt = $conn->prepare($query);
        $searchTerm = "%$searchTerm%";
        $stmt->bindParam(':search_term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function closeConnection() {
        $this->db = null;
    }
}
?>