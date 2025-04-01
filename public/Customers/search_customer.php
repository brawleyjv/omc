<?php
require_once realpath(dirname(__FILE__, 3) . '/config.php'); // Adjust path to config.php

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
    $query = trim($_GET['query']);

    // Database connection using PDO
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM customers WHERE name LIKE ? OR email LIKE ?";
        $stmt = $db->prepare($sql);
        $searchTerm = "%" . $query . "%";
        $stmt->execute([$searchTerm, $searchTerm]);

        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header("Content-Type: application/json");
        echo json_encode($customers);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["error" => "Failed to search customers"]);
    }
    exit();
}
?>
