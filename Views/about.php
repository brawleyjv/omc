<?php
require_once realpath(dirname(__FILE__) . '/../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css"> <!-- Ensure BASE_URL is used correctly -->
</head>
<body>
<?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
    <img src="<?php echo BASE_URL; ?>public/images/login-image.png" alt="Login Image Thumbnail" class="thumbnail-image" style="max-width: 150px;">        <h1>About This Program</h1>
        <p>This program was originally written by Jack Brawley to assist in the management of all aspects of Ozark Made Crafts (OMC). However, it was designed to be versatile and adaptable, so that anyone could use it, regardless of their business.</p>
        <p>Ozark Made Crafts is an ambition of Jack, his wife Kathie, his brother Tim, and Tim's wife Angie. Each of them plays a crucial role in the success of OMC. This program aims to help keep the team organized and achieve the success they are striving for.</p>
        <p>We hope this program serves you well in managing your business and contributes to your success.</p>
    </div>
    <script src="<?php echo BASE_URL; ?>public/js/scripts.js"></script>
</body>
</html>

