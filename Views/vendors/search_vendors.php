<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Vendors</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path to styles.css -->
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
        }
        .btn.styled-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
        .btn.close-btn {
            background-color: #DC3545;
        }
        .btn.close-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Include header -->
    <div class="container">
        <h1>Search Vendors</h1>
        <form action="<?php echo BASE_URL; ?>public/Vendors/search_vendors.php" method="get">
            <div class="form-group">
                <label for="search_term">Vendor Name:</label>
                <input type="text" id="search_term" name="search_term" placeholder="Enter vendor name or partial name" required>
            </div>
            <div class="button-container">
                <button type="submit" class="btn styled-btn">Search</button>
                <a href="<?php echo BASE_URL; ?>Views/vendors/index.php" class="btn styled-btn close-btn">Close</a>
            </div>
        </form>
    </div>
</body>
</html>
