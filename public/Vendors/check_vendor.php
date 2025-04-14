<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

use MyApp\Models\Database; // Add the correct namespace for the Database class

$vendor = trim($_POST['vendor'] ?? ''); // Trim and ensure vendor is not null

if (empty($vendor)) {
    error_log("Error: Vendor name is empty."); // Log the error
    echo "Error: Vendor name cannot be empty.";
    exit;
}

$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD); // Use DB_PASSWORD instead of DB_PASSWORD
$conn = $database->getConnection();
if ($conn === null) {
    die('Database connection failed.');
}

$sql = "SELECT * FROM vendors WHERE Vendor = :vendor";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':vendor', $vendor, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo 'exists';
} else {
    echo 'not exists';
}

// No need to explicitly close the connection in PDO
?>
