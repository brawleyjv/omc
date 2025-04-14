<?php
namespace MyApp\Services;

require_once realpath(dirname(__FILE__) .'/../config.php'); // Include the configuration file

ini_set('memory_limit', '1024M'); // Increase memory limit to 1GB
ini_set('max_execution_time', '300'); // Increase execution time to 300 seconds (5 minutes)

class FileManager {
    public function downloadFile($url, $savePath) {
        // Ensure the save path is adjusted to point to C:\xampp\htdocs\
        $savePath = str_replace('/OMC/', '/', $savePath); // Adjust path to root directory
        $fileContent = file_get_contents($url);
        if ($fileContent === false) {
            throw new \Exception("Failed to download file from $url");
        }

        if (file_put_contents($savePath, $fileContent) === false) {
            throw new \Exception("Failed to save file to $savePath");
        }
    }

    public function backupDatabase($databaseName, $username, $password) {
        $backupFolder = BASE_PATH . 'public/db_backup/backup_files/';
        $backupFile = $backupFolder . 'omc_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

        if (!is_dir($backupFolder)) {
            if (!mkdir($backupFolder, 0775, true)) {
                throw new \Exception("Failed to create backup folder: $backupFolder");
            }
        }

        $mysqldumpPath = "/usr/bin/mysqldump"; // Update this path if necessary
        $command = !empty($password)
            ? "\"$mysqldumpPath\" --no-tablespaces --user=$username --password=$password $databaseName > \"$backupFile\""
            : "\"$mysqldumpPath\" --no-tablespaces --user=$username $databaseName > \"$backupFile\"";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Database backup failed. Command: $command");
        }

        return $backupFile;
    }
}
?>
