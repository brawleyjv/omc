<?php
require_once realpath(dirname(__FILE__) . '/../../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/Bom.php';

use MyApp\Models\Database;
use MyApp\Models\Bom;

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null; // Use 'id' instead of 'material_id'
    $projectId = $_GET['project_id'] ?? null;

    if (!$id || !$projectId) {
        throw new Exception('Material ID and Project ID are required.');
    }

    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn = $database->getConnection();
    $bomModel = new Bom($conn);

    $query = "DELETE FROM bom WHERE material_id = :material_id AND project_id = :project_id"; // Use 'project_id'
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':material_id', $id, PDO::PARAM_INT); // Use 'material_id'
    $stmt->bindValue(':project_id', $projectId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete the material from the BOM.');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred while removing the material: ' . $e->getMessage()]);
}
?>
