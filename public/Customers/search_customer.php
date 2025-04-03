<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__, 3) . '/config.php'); // Corrected syntax

$query = $_GET['query'] ?? ''; // Initialize query variable

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($query)) {
    $query = trim($query);

    // Database connection using PDO
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM customers WHERE name LIKE ? OR email LIKE ?";
        $stmt = $db->prepare($sql);
        $searchTerm = "%" . $query . "%";
        $stmt->execute([$searchTerm, $searchTerm]);

        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($customers)) {
            error_log("No customers found for query: $query");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $customers = [];
    }
} else {
    $customers = [];
}
?>
