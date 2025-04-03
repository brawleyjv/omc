<?php
require_once realpath(dirname(__FILE__) . '/config.php'); // Adjust path to config.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $project = trim($_POST['project_name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $notes = trim($_POST['notes']);

    if (empty($id) || empty($name)) {
        header("Location: " . BASE_URL . "Views/customers/edit_customer.php?id=$id&error=Name is required");
        exit();
    }

    // Database connection using PDO
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "UPDATE customers SET name = ?, Project = ?, address = ?, city = ?, state = ?, zip = ?, phone = ?, email = ?, notes = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $project, $address, $city, $state, $zip, $phone, $email, $notes, $id]);

        header("Location: " . BASE_URL . "Views/customers/list_customers.php?success=Customer updated successfully");
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: " . BASE_URL . "Views/customers/edit_customer.php?id=$id&error=Failed to update customer");
    }
    exit();
}
?>
