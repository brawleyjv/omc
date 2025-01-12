<?php
// filepath: /c:/xampp/htdocs/OMC/public/materials/index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials Menu</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <style>
        .center-title {
            text-align: center;
            margin-top: 100px; /* Add some margin to ensure the title is visible */

        }
        .menu-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../../views/header.php'; ?>
    <div class="container">
    <h1 class="title">Ozark Made Project Management System</h1>
        <h1 class="title">Material Menu</h1>
        <div class="button-container">
            <a href="add_material.php" class="btn styled-btn">Add Material</a>
            <a href="search_materials.php" class="btn styled-btn">Search Material</a>
            
            <a href="list_materials.php" class="btn styled-btn">List Material</a>
                </div>
</body>
</html>