<?php
require_once __DIR__ . '/../../Globals/Config.php';
require_once __DIR__ . '/../../Models/Database.php';

use MyApp\Models\Database;
use Globals\Config;

try {
    $query = $_GET['query'];

    if (empty($query)) {
        throw new Exception('Query parameter is missing.');
    }

    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    $stmt = $database->getConnection()->prepare("SELECT id, material_name, type, length, width, thickness FROM materials WHERE type LIKE ?");
    $stmt->execute(['%' . $query . '%']);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($materials);
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'An error occurred while fetching materials: ' . $e->getMessage()]);
}
?>
