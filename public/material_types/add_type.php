<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Models/Database.php';

use MyApp\Models\Database;
use Globals\Config;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = $_POST['type_name'];

    if (empty($type_name)) {
        echo "<script>alert('Type Name is required.'); window.history.back();</script>";
        exit();
    }

    $database = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS);
    $stmt = $database->getConnection()->prepare("INSERT INTO material_types (type_name) VALUES (?)");
    $stmt->execute([$type_name]);

    echo "<script>alert('Material Type added successfully.'); window.location.href = 'http://localhost/omc/Views/material_types/add_type.php';</script>";
}
?>
