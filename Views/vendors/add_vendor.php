<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vendor</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Corrected path to styles.css -->
</head>
<body>
    <?php include BASE_PATH . 'Views/header.php'; ?> <!-- Use BASE_PATH -->
    <div class="container">
        <h1 class="title">Add Vendor</h1>
        <form id="add-vendor-form" action="<?php echo BASE_URL; ?>public/Vendors/insert_vendor.php" method="post">
            <label for="vendor">Vendor:</label>
            <input type="text" id="vendor" name="vendor" required><br>
            
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone"><br>
            
            <label for="web_address">Web Address:</label>
            <input type="text" id="web_address" name="web_address"><br>
            
            <label for="mailing_address">Mailing Address:</label>
            <input type="text" id="mailing_address" name="mailing_address"><br>
            
            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address"><br>
            <button type="submit" class="btn styled-btn">Submit</button>
        </form>
    </div>
</body>
</html>
