<?php
require_once realpath(dirname(__FILE__) . '/../../Config.php'); // Updated to use realpath
require_once BASE_PATH . '/Models/Database.php';

// ...existing code...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css">
    <!-- ...existing code... -->
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?>
    <!-- ...existing code... -->
</body>
</html>
