<?php
require_once realpath(dirname(__FILE__) . '/../../Config.php'); // Updated to use realpath
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = $_POST['type_name'];

    if (empty($type_name)) {
        echo "<script>alert('Type Name is required.'); window.history.back();</script>";
        exit();
    }

    $database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD); // Removed Globals\Config
    $stmt = $database->getConnection()->prepare("INSERT INTO material_types (type_name) VALUES (?)");
    $stmt->execute([$type_name]);

    echo "<script>alert('Material Type added successfully.'); window.location.href = '" . BASE_URL . "/Views/material_types/add_type.php';</script>"; // Use BASE_URL
}
?>
