require_once realpath(dirname(__FILE__) . '/../../config.php'); // Load config
require_once BASE_PATH . '/Models/Database.php'; // Ensure Database.php is included

use MyApp\Models\Database;

// Initialize database connection
$database = new Database();
$db = $database->getConnection(); // Ensure $db is a PDO instance

// Use credentials from config.php
$host = DB_HOST;
$username = DB_USER;
$password = DB_PASSWORD;
$databaseName = DB_NAME;

// Set backup folder and file location
$backupFolder = BASE_PATH . 'public/db_backup/backup_files/';
$backupFile = $backupFolder . 'omc_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Ensure backup directory exists
if (!is_dir($backupFolder)) {
    if (!mkdir($backupFolder, 0775, true)) {
        error_log("❌ Failed to create backup folder: $backupFolder", 3, BASE_PATH . 'logs/error.log');
        die("❌ Backup folder creation failed. See error logs.");
    }
}

// Find and set correct `mysqldump` path
$mysqldumpPath = "/usr/bin/mysqldump"; // Update this based on IONOS server
if (!file_exists($mysqldumpPath)) {
    error_log("❌ mysqldump not found at $mysqldumpPath", 3, BASE_PATH . 'logs/error.log');
    die("❌ mysqldump command not found. Verify the correct path.");
}

// Prepare the mysqldump command
if (!empty($password)) {
    $command = escapeshellcmd("\"$mysqldumpPath\" --no-tablespaces --host=$host --user=$username --password=$password $databaseName > \"$backupFile\"");
} else {
    $command = escapeshellcmd("\"$mysqldumpPath\" --no-tablespaces --host=$host --user=$username $databaseName > \"$backupFile\"");
}

// Execute the backup command
exec($command, $output, $returnCode);

// Validate backup process
if ($returnCode === 0) {
    echo "✅ Backup saved to: " . $backupFile;
} else {
    error_log("❌ Backup failed. Command: $command", 3, BASE_PATH . 'logs/error.log');
    error_log("❌ Output: " . implode("\n", $output), 3, BASE_PATH . 'logs/error.log');
    echo "❌ Backup failed. Check logs for details.";
}
?>