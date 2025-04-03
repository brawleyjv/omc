<?php
// ...existing code...

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn = new mysqli('localhost', 'username', 'password', 'database_name'); // Update with your DB credentials

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Ensure the column name matches your database schema (e.g., 'customer_id' instead of 'id')
    $sql = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "Customer deleted successfully.";
            } else {
                echo "No customer found with the given ID.";
            }
        } else {
            error_log("Error executing delete query: " . $stmt->error);
            echo "Error executing delete query.";
        }
        $stmt->close();
    } else {
        error_log("Error preparing statement: " . $conn->error);
        echo "Error preparing statement.";
    }

    $conn->close();
} else {
    error_log("Delete ID not set in the request.");
    echo "Invalid request.";
}

// ...existing code...
?>
