<?php
require_once __DIR__ . '/../../Globals/Config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <style>
        .title {
            text-align: center;
            margin-top: 200px; /* Lower the title by 200px */
        }
        .button-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px; /* Add margin to separate buttons from title */
        }
        .btn.styled-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            margin: 10px; /* Add margin to separate buttons */
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    
    <h1 class="title">Project Menu</h1>
    <div class="button-container">
        <a href="../../Views/projects/add_project.php" class="btn styled-btn">Add Project</a> <!-- Update path -->
        <a href="../../Views/projects/search_projects.php" class="btn styled-btn">Search Project</a> <!-- Updated link -->
        <a href="list_projects.php" class="btn styled-btn">List Projects</a>
        <a href="estimate.php" class="btn styled-btn">Estimate</a>
        <a href="invoice.php" class="btn styled-btn">Invoice</a>
        <a href="view_project.php" class="btn styled-btn">View Project</a> <!-- New button to view project -->
    </div>
</body>
</html>