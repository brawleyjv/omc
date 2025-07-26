<?php
require_once __DIR__ . '/../../config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/ProjectController.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$project_id = $_GET['project_id'];

// Validate and sanitize project_id
if (!is_numeric($project_id)) {
    die("Invalid project ID");
}

$delete_bom_sql = "DELETE FROM bom WHERE project_id = ?";
$stmt = $conn->prepare($delete_bom_sql);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$stmt->close();

$delete_project_sql = "DELETE FROM projects WHERE id = ?";
$stmt = $conn->prepare($delete_project_sql);
$stmt->bind_param("i", $project_id);
if ($stmt->execute()) {
    $stmt->close();
    echo "<script>
            alert('Project and associated BOM entries deleted successfully.');
            window.location.href = '" . BASE_URL . "public/projects/list_projects.php';
          </script>";
} else {
    $stmt->close();
    echo "<script>
            alert('Error deleting project: " . $stmt->error . "');
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
