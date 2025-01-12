<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects Main</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Corrected path -->
</head>
<body>
    <?php include '../../views/header.php'; ?> <!-- Corrected path to header file -->
    <div class="container">
    <h1 class="title">Ozark Made Project Management System</h1>
        <h1 class="title">Projects Menu</h1>
        <div class="button-container">
            <a href="add_project.php" class="btn styled-btn">Add Project</a>
            <a href="boardfeet.php" class="btn styled-btn">Board Feet</a>
            
            <a href="list_projects.php" class="btn styled-btn">List Projects</a>
            <a href="../../views/projects/search_projects.php" class="btn styled-btn">Search Projects</a>
            <a href="estimate.php" class="btn styled-btn">Estimate</a> <!-- Added button for Estimate -->
            <a href="view_project.php" class="btn styled-btn">View Project</a> <!-- New button to view project -->
        </div>
    </div>
</body>
</html>
<?php
// Close the PDO connection
$conn = null;
?>