<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Correct relative path to config.php
require_once BASE_PATH . 'Controllers/VendorController.php'; // Correct relative path to VendorController.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css"> <!-- Ensure BASE_URL is used correctly -->
    <style>
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
        }
        .btn.styled-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 16px;
            flex: 1; /* Make buttons stretch evenly */
            text-align: center;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Include header -->
    <div class="container">
        <h1>Vendor Management</h1>
        <p>Manage your vendors using the options below:</p>
        <div class="button-container">
            <a href="<?php echo BASE_URL; ?>Views/vendors/add_vendor.php" class="btn styled-btn">Add Vendor</a>
            <a href="<?php echo BASE_URL; ?>Views/vendors/list_vendors.php" class="btn styled-btn">List Vendors</a>
            <a href="<?php echo BASE_URL; ?>Views/vendors/search_vendors.php" class="btn styled-btn">Search Vendors</a>
            <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn styled-btn" style="background-color: #DC3545; color: white;">Close</a> <!-- Close button -->
        </div>
    </div>
</body>
</html>
