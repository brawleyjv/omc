<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Views/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Ensure BASE_URL points to localhost/omc -->
    <style>
        .title {
            text-align: center;
        }
        .form-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .form-buttons .button {
            width: 48%;
        }
        .form-buttons .cancel-button {
            background-color: gray;
            color: white;
            text-align: center;
            padding: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .form-buttons .button + .button {
            margin-left: 100px; /* Add 100px padding between the buttons */
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <h1 class="title">Add Material</h1> <!-- Center the title -->
    <form id="materialForm" action="<?php echo BASE_URL; ?>public/materials/create.php" method="post"> <!-- Updated the form action -->
        <div class="form-buttons">
            <a href="<?php echo BASE_URL; ?>public/materials/index.php" class="button cancel-button">Cancel</a> <!-- Updated the cancel button link -->
            <input type="submit" value="Add" class="button"> <!-- Add button -->
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" id="description" name="description" required>
        </div>
        <div class="form-group">
            <label for="item_no">Item No:</label>
            <input type="text" id="item_no" name="item_no" required>
        </div>
        <div class="form-group">
            <label for="vendor">Vendor:</label>
            <input type="text" id="vendor" name="vendor" required>
        </div>
        <div class="form-group">
            <label for="type">Type:</label>
            <input type="text" id="type" name="type" required>
        </div>
        <div class="form-group">
            <label for="length">Length:</label>
            <input type="number" id="length" name="length" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="width">Width:</label>
            <input type="number" id="width" name="width" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="thickness">Thickness:</label>
            <input type="number" id="thickness" name="thickness" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>
        </div>
        <div class="form-group">
            <label for="item_url">Item URL:</label>
            <input type="url" id="item_url" name="item_url">
        </div>
        <div class="form-group">
            <label for="image_url">Image URL:</label>
            <input type="url" id="image_url" name="image_url">
        </div>
    </form>
</body>
</html>
