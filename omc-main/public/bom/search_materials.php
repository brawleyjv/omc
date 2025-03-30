<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Ensure Config is included

require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/Models/Database.php';

use MyApp\Models\Database;

try {
    $search_query = $_GET['query'];

    if (empty($search_query)) {
        throw new Exception('Search query is missing.');
    }

    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $stmt = $database->getConnection()->prepare("SELECT id, material_name, type FROM materials WHERE material_name LIKE ?");
    $stmt->execute(['%' . $search_query . '%']);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($materials);
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'An error occurred while searching for materials: ' . $e->getMessage()]);
}
?>
