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
            gap: 20px; /* Add spacing between buttons */
            margin-top: 20px; /* Add margin above the buttons */
        }
        .btn.styled-btn {
            padding: 10px 20px; /* Adjust padding for better appearance */
            font-size: 16px; /* Ensure consistent font size */
            text-align: center; /* Center text in the button */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center;">Customer Management</h1> <!-- Center the title -->
        <div class="button-container">
            <a href="<?php echo BASE_URL; ?>/Views/customers/add_customer.php" class="btn styled-btn">Add Customer</a>
            <a href="<?php echo BASE_URL; ?>/Views/customers/search_customer.php" class="btn styled-btn">Search Customer</a>
            <a href="<?php echo BASE_URL; ?>/Views/customers/list_customers.php" class="btn styled-btn">List Customers</a>
        </div>
        <div class="button-container">
            <a href="<?php echo BASE_URL; ?>/Views/main.php" class="btn styled-btn red">Back to Main</a>
        </div>
    </div>
</body>
</html>
