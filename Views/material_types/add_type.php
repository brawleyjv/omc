<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material Type</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css">
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <h1 class="title">Add Material Type</h1>
    <form action="<?php echo BASE_URL; ?>public/material_types/add_type.php" method="post">
        <div class="form-group">
            <label for="type_name">Type Name:</label>
            <input type="text" id="type_name" name="type_name" required>
        </div>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
