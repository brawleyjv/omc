<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Use BASE_URL -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <div class="container">
        <h1 class="title">Add Equipment</h1>
        <form action="<?php echo BASE_URL; ?>public/equipment/insert_equipment.php" method="post"> <!-- Use BASE_URL -->
            <!-- ...existing code... -->
        </form>
    </div>
</body>
</html>