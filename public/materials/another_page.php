<?php
require_once realpath(dirname(__FILE__) . '/../../../config.php'); // Updated to use realpath
include realpath(dirname(__FILE__) . '/../../../Views/header.php'); // Updated to use realpath
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Another Page</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css"> <!-- Updated to use correct BASE_URL -->
</head>
<body>
    <h1>Another Page</h1>
    <a href="<?php echo BASE_URL; ?>public/materials/index.php">Back to Materials Index</a>
</body>
</html>
