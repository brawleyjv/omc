<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';

use MyApp\Models\Database;
use Globals\Config;

try {
    $material_id = $_GET['material_id'];

    if (empty($material_id)) {
        throw new Exception('Material ID is missing.');
    }

    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
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
