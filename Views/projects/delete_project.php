<?php
require_once __DIR__ . '/../../../config.php'; // Updated path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css">
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Delete Project</h1>
        <p>Are you sure you want to delete this project?</p>
        <form action="<?php echo BASE_URL; ?>public/projects/delete_project.php" method="post">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($_GET['project_id'] ?? ''); ?>">
            <button type="submit" class="btn styled-btn red">Delete</button>
            <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn styled-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
