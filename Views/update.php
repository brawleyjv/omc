<?php
require_once realpath(dirname(__FILE__) . '/../config.php'); // Updated to use realpath(dirname(__FILE__))
require_once BASE_PATH . '/Views/header.php'; // Updated to use BASE_PATH
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Include the CSS file -->
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../Views/header.php'); ?> <!-- Updated to use realpath -->
    <div class="container">
        <h1 class="title">Update System</h1>
        
        <!-- Notice for stable internet connection -->
        <div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            <strong>Notice:</strong> Please ensure a stable internet connection before starting the update process.
        </div>

        <p>Follow the steps below to update the system:</p>
        <p><strong>Instructions:</strong></p>
        <ol>
            <li><strong>Download Update Package:</strong> Click the "Download Update Package" button below to download the latest update package. The file will be saved to the root directory: <code>C:\xampp\htdocs\main.zip</code>.</li>
            <li><strong>Extract the Package:</strong> Manually extract the contents of <code>main.zip</code> into the <code>C:\xampp\htdocs</code> directory. Ensure that the files overwrite the existing ones.</li>
            <li><strong>Backup Database:</strong> Use the "Backup Database" button to create a backup of the current database. The backup file will be saved in the root directory: <code>C:\xampp\htdocs\</code>.</li>
            <li><strong>Run Database Update Script:</strong> Open MySQL and manually run the SQL script provided in the update package to update the database.</li>
        </ol>
        <p style="color: red; font-weight: bold;">Important: Ensure you have a backup of your files and database before proceeding with the update.</p>
        <div class="button-container">
            <a href="<?php echo BASE_URL; ?>public/update.php?action=download_zip&savePath=<?php echo urlencode($_SERVER['DOCUMENT_ROOT']); ?>" class="btn styled-btn">Download Update Package</a>
            <a href="<?php echo BASE_URL; ?>public/update.php?action=backup_database" class="btn styled-btn">Local bu Database</a>
            <a href="<?php echo BASE_URL; ?>public/db_backup/remote_db_bu.php" class="btn styled-btn">Remote DB Backup</a>
            <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn cancel-btn">Cancel</a>
        </div>
        <form method="post" action="update.php">
            
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <form method="get" action="<?php echo BASE_URL; ?>public/update.php">
                <input type="hidden" name="action" value="import_database">
                <button type="submit" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">Import Database</button>
            </form>
        </div>
    </div>
</body>
</html>
