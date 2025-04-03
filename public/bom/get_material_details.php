<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once realpath(dirname(__FILE__) . '/../../Models/Database.php');

use MyApp\Models\Database;

try {
    $id = $_GET['id']; // Use 'id' instead of 'material_id'

    if (empty($id)) {
        throw new Exception('Material ID is missing.');
    }

    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $stmt = $database->getConnection()->prepare("SELECT material_id, material_name, type, length, width, thickness FROM materials WHERE material_id = ?");
    $stmt->execute([$id]); // Use 'material_id'
    $material = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$material) {
        throw new Exception('Material not found.');
    }

    header('Content-Type: application/json');
    echo json_encode($material);
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'An error occurred while fetching material details: ' . $e->getMessage()]);
}
?>
