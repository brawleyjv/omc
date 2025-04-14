<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

use MyApp\Models\Database;

// Debugging: Log the entire $_POST array to verify the submitted data
error_log("Debug: POST data - " . print_r($_POST, true));

// Safely retrieve form data and handle null values
$vendor = isset($_POST['vendor']) ? trim($_POST['vendor']) : ''; // Default to an empty string
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : null; // Allow null for optional fields
$web_address = isset($_POST['web_address']) ? trim($_POST['web_address']) : null; // Allow null for optional fields
$mailing_address = isset($_POST['mailing_address']) ? trim($_POST['mailing_address']) : null; // Allow null for optional fields
$email_address = isset($_POST['email_address']) ? trim($_POST['email_address']) : null; // Allow null for optional fields

// Debugging: Log the vendor name to verify its value
error_log("Debug: Vendor name - '$vendor'");

if (empty($vendor)) {
    error_log("Error: Vendor name is empty."); // Log the error
    echo "Error: Vendor name cannot be empty.";
    exit;
}

try {
    // Initialize the database connection
    $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Use DB_PASSWORD
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // Debugging: Log the input data
    error_log("Debug: Vendor data - Vendor: $vendor, Phone: $phone, Web Address: $web_address, Mailing Address: $mailing_address, Email Address: $email_address");

    // Prepare the SQL query
    $sql = "INSERT INTO vendors (Vendor, Phone, Web_Address, Mailing_Address, Email_Address) VALUES (:vendor, :phone, :web_address, :mailing_address, :email_address)";
    $stmt = $conn->prepare($sql);

    // Execute the query
    $stmt->execute([
        ':vendor' => $vendor,
        ':phone' => $phone,
        ':web_address' => $web_address,
        ':mailing_address' => $mailing_address,
        ':email_address' => $email_address
    ]);

    // Check if the row was inserted
    if ($stmt->rowCount() > 0) {
        error_log("Debug: Vendor added successfully.");
        header('Location: ' . BASE_URL . 'Views/vendors/index.php'); // Redirect to vendors index page
        exit;
    } else {
        throw new Exception("Failed to add vendor. No rows affected.");
    }
} catch (PDOException $e) {
    // Log PDO exceptions
    error_log("PDOException: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
} catch (Exception $e) {
    // Log general exceptions
    error_log("Exception: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>
