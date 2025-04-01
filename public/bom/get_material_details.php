<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once realpath(dirname(__FILE__) . '/../../Models/Database.php');

use MyApp\Models\Database;

try {
    $material_id = $_GET['material_id'];

    if (empty($material_id)) {
        throw new Exception('Material ID is missing.');
    }

    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $stmt = $database->getConnection()->prepare("SELECT m.material_name, m.type, m.length, m.width, m.thickness FROM materials m WHERE m.id = ?");
    $stmt->execute([$material_id]);
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
