<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$project_id = $_GET['project_id'];

$delete_bom_sql = "DELETE FROM bom WHERE project_id = '$project_id'";
$conn->query($delete_bom_sql);

$delete_project_sql = "DELETE FROM projects WHERE id = '$project_id'";
if ($conn->query($delete_project_sql) === TRUE) {
    echo "<script>
            alert('Project and associated BOM entries deleted successfully.');
            window.location.href = '" . BASE_URL . "public/projects/list_projects.php';
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <h1>Delete Project</h1>
    <!-- Add your content here -->
</body>
</html>
