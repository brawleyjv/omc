<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Include the configuration file
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/header.php'; // Include the header
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
    <div class="container">
        <h1 class="title">Update System</h1>
        
        <!-- Notice for stable internet connection -->
        <div class="alert alert-info" style="background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            <strong>Notice:</strong> Please ensure a stable internet connection before starting the update process.
        </div>

        <p>Use the buttons below to update, restore, or manually back up the system.</p>
        <p><strong>Instructions:</strong></p>
        <ul>
            <li><strong>Download and Extract Update Package:</strong> Downloads the latest update package from the repository, extracts its contents into the system folder, and replaces the existing files.</li>
            <li><strong>Update Database:</strong> Creates a backup of the current database, downloads the latest database update file, and applies it to the system.</li>
            <li><strong>Restore Database:</strong> Allows you to restore the database from a previously created backup file.</li>
            <li><strong>Manual Backup:</strong> Creates a manual backup of the current database, which can be restored later if needed.</li>
            <li><strong>Export Database:</strong> Exports the database to a clean SQL file for testing or backup purposes.</li>
        </ul>
        <div class="button-container">
            <a href="<?php echo BASE_URL; ?>public/update.php?action=download_zip" class="btn styled-btn">Download and Extract Update Package</a>
            <a href="<?php echo BASE_URL; ?>public/update.php?action=manual_backup" class="btn styled-btn">Update Database</a> <!-- Changed label -->
            <a href="<?php echo BASE_URL; ?>public/update.php?action=update_database" class="btn styled-btn">Backup Database</a> <!-- Changed label -->
            <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn cancel-btn">Cancel</a>
        </div>
        <form method="post" action="update.php">
            
        </form>
    </div>
</body>
</html>
