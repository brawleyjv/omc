<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Services/DatabaseManager.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Services/FileManager.php';

class UpdateController {
    private $dbManager;
    private $fileManager;

    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->fileManager = new FileManager();
    }

    public function downloadZip() {
        error_log("UpdateController: downloadZip() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Download process started...</p>";
            ob_flush();
            flush();

            $zipUrl = "https://github.com/brawleyjv/omc/archive/refs/heads/main.zip";
            $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/main.zip';
            $extractPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC';

            // Assign a value to $githubApiUrl
            $githubApiUrl = 'https://api.github.com'; // Example URL, replace with the actual value if needed

            // Step 1: Download the ZIP file
            error_log("UpdateController: Attempting to download file from $zipUrl to $savePath.");
            $this->fileManager->downloadFile($zipUrl, $savePath);
            echo "<p style='color: green; text-align: center;'>Download complete. File saved to: $savePath</p>";
            ob_flush();
            flush();

            // Step 2: Extract the ZIP file
            error_log("UpdateController: Attempting to extract file to $extractPath.");
            $this->fileManager->extractZip($savePath, $extractPath);
            echo "<p style='color: green; text-align: center;'>Extraction complete. Files extracted to: $extractPath</p>";
            ob_flush();
            flush();
        } catch (Exception $e) {
            // Log and display error message
            error_log("UpdateController: Error in downloadZip(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }

    public function updateDatabase() {
        error_log("UpdateController: updateDatabase() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Starting database backup process...</p>";
            ob_flush();
            flush();

            $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/';
            $backupPath = $savePath . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

            // Step 1: Backup the current database
            error_log("UpdateController: Creating a backup of the current database.");
            echo "<p style='color: blue; text-align: center;'>Creating a backup of the current database...</p>";
            ob_flush();
            flush();
            $this->dbManager->backupDatabase($backupPath);
            echo "<p style='color: green; text-align: center;'>Database backup created successfully: $backupPath</p>";
            ob_flush();
            flush();

            // Add a return button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='/OMC/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Return to Update Page</a>
                  </div>";
            ob_flush();
            flush();
        } catch (Exception $e) {
            error_log("UpdateController: Error in updateDatabase(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }

    public function manualBackup() {
        try {
            error_log("UpdateController: manualBackup() method called."); // Log method call
            echo "<p style='color: blue; text-align: center;'>Database update process initiated...</p>";
            ob_flush();
            flush();

            // Find the .sql file with the newest timestamp in the /OMC/ directory
            $directory = $_SERVER['DOCUMENT_ROOT'] . '/OMC/';
            $files = glob($directory . '*.sql');
            if (empty($files)) {
                throw new Exception("No .sql files found in the directory: $directory");
            }

            // Sort files by modification time, newest first
            usort($files, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            $newestSqlFile = $files[0];
            error_log("UpdateController: Newest .sql file found: $newestSqlFile");
            echo "<p style='color: blue; text-align: center;'>Processing the newest SQL file: " . basename($newestSqlFile) . "</p>";
            ob_flush();
            flush();

            // Process the SQL file into the database
            $this->dbManager->importDatabase($newestSqlFile);

            echo "<p style='color: green; text-align: center;'>Database updated successfully using: " . basename($newestSqlFile) . "</p>";
            ob_flush();
            flush();

            // Add a return button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='/OMC/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Return to Update Page</a>
                  </div>";
            ob_flush();
            flush();
        } catch (Exception $e) {
            error_log("UpdateController: Error in manualBackup(): " . $e->getMessage()); // Log the error
            echo "<p style='color: red; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }
}
?>
