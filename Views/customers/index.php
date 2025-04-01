<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated to use realpath
require_once BASE_PATH . '/Views/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/styles.css"> <!-- Updated to use BASE_URL -->
    <style>
        .button-container {
            display: flex;
            justify-content: center; /* Center buttons horizontally */
            flex-wrap: wrap; /* Allow buttons to wrap if needed */
            gap: 10px; /* Add spacing between buttons */
        }
        .btn.styled-btn {
            width: 200px; /* Optional: Set a fixed width for buttons */
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Customer Management</h1>
        <div class="button-container">
        <a href="<?php echo BASE_URL; ?>/Views/customers/add_customer.php" class="btn styled-btn">Add Customer</a>
            <a href="<?php echo BASE_URL; ?>/Views/customers/search_customer.php" class="btn styled-btn">Search Customer</a>
        <a href="<?php echo BASE_URL; ?>/Views/customers/list_customers.php" class="btn styled-btn">List Customers</a>
        </div>
    </div>
</body>
</html>
