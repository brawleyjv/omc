<?php
include '../config.php'; // Include the configuration file for database connection

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$project_id = $_GET['project_id'];

// Delete BOM entries for the project
$delete_bom_sql = "DELETE FROM bom WHERE project_id = '$project_id'";
$conn->query($delete_bom_sql);

// Delete the project
$delete_project_sql = "DELETE FROM projects WHERE id = '$project_id'";
if ($conn->query($delete_project_sql) === TRUE) {
    echo "<script>
            alert('Project and associated BOM entries deleted successfully.');
            window.location.href = 'list_projects.php';
          </script>";
} else {
    echo "<script>
            alert('Error deleting project: " . $conn->error . "');
            window.history.back();
          </script>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Project</title>
    <link rel="stylesheet" href="../public/css/"> <!-- Corrected the path to the CSS file in the root directory -->
</head>
<body>
    <?php include '../header.php'; ?> <!-- Include the header file -->
    <h1>Delete Project</h1>
    <!-- Add your content here -->
</body>
</html>
