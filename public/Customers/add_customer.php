<?php
require_once realpath(dirname(__FILE__, 3) . '/config.php'); // Adjust path to config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $project = trim($_POST['project_name']); // Match column name "Project"
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $notes = trim($_POST['notes']);

    if (empty($name)) {
        header("Location: " . BASE_URL . "Views/customers/add_customer.php?error=Name is required");
        exit();
    }

    // Database connection using PDO
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "INSERT INTO customers (name, Project, address, city, state, zip, phone, email, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $project, $address, $city, $state, $zip, $phone, $email, $notes]);

        header("Location: " . BASE_URL . "Views/customers/index.php?success=Customer added successfully");
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/customers/add_customer.php?error=Failed to add customer");
    }
    exit();
}
?>
