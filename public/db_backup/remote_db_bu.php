<?php
require_once realpath(dirname(__FILE__) . '/../config.php'); // Include configuration

// Database credentials
$host = "your_ionos_host"; // Example: db1234567890.hosting-data.io
$username = "your_db_user";
$password = "your_db_password";
$databaseName = "OMC_DB";

// Set backup folder and file location
$backupFolder = BASE_PATH . 'public/db_backup/backup_files/';
$backupFile = $backupFolder . 'omc_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Ensure the directory exists
if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0775, true); // Create folder if it doesn’t exist
}

// Run the mysqldump command
$command = "mysqldump --no-tablespaces --host=$host --user=$username --password=$password $databaseName > \"$backupFile\"";
exec($command, $output, $returnCode);

// Check the result
if ($returnCode === 0) {
    echo "✅ Backup saved to: " . $backupFile;
} else {
    echo "❌ Backup failed. Check permissions or server settings.";
}
?>
