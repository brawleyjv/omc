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
        try {
            $githubApiUrl = "https://api.github.com/repos/brawleyjv/omc/contents/"; // GitHub API URL for the repository
            $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/';
            $backupPath = $savePath . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

            // Step 1: Backup the current database
            $this->dbManager->backupDatabase($backupPath);
            echo "<p style='color: green; text-align: center;'>Database backup created successfully: $backupPath</p>";

            // Step 2: Fetch the list of files from the GitHub repository
            $context = stream_context_create([
                "http" => [
                    "header" => "User-Agent: PHP"
                ]
            ]);
            $response = file_get_contents($githubApiUrl, false, $context);
            if ($response === false) {
                throw new Exception("Failed to fetch the file list from GitHub.");
            }

            $files = json_decode($response, true);
            if (!is_array($files)) {
                throw new Exception("Invalid response from GitHub API.");
            }

            // Step 3: Find the newest .sql file
            $newestSqlFile = null;
            $newestTimestamp = 0;
            foreach ($files as $file) {
                if (isset($file['name']) && pathinfo($file['name'], PATHINFO_EXTENSION) === 'sql') {
                    $timestamp = strtotime($file['name']);
                    if ($timestamp > $newestTimestamp) {
                        $newestTimestamp = $timestamp;
                        $newestSqlFile = $file;
                    }
                }
            }

            if ($newestSqlFile === null) {
                throw new Exception("No .sql files found in the GitHub repository.");
            }

            // Step 4: Download the newest .sql file
            $sqlUrl = $newestSqlFile['download_url'];
            $sqlFilePath = $savePath . basename($newestSqlFile['name']);
            $this->fileManager->downloadFile($sqlUrl, $sqlFilePath);
            echo "<p style='color: green; text-align: center;'>Downloaded the newest .sql file: $sqlFilePath</p>";

            // Step 5: Drop all tables and execute the .sql file
            $this->dbManager->importDatabase($sqlFilePath);
            echo "<p style='color: green; text-align: center;'>Database updated successfully using: $sqlFilePath</p>";
        } catch (Exception $e) {
            echo "<p style='color: red; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    public function restoreBackup() {
        if (isset($_POST['backup_file']) && !empty($_POST['backup_file'])) {
            $backupFile = $_SERVER['DOCUMENT_ROOT'] . '/OMC/' . basename($_POST['backup_file']);
            try {
                $this->dbManager->restoreDatabase($backupFile);
                echo "<p style='color: green; text-align: center;'>Database restored successfully from: $backupFile</p>";
            } catch (Exception $e) {
                echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red; text-align: center;'>No backup file selected for restoration.</p>";
        }
    }

    public function manualBackup() {
        $backupPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/backup_' . date('Y-m-d_H-i-s') . '.sql';
        try {
            $this->dbManager->backupDatabase($backupPath);
            echo "<p style='color: green; text-align: center;'>Manual database backup created successfully: $backupPath</p>";
        } catch (Exception $e) {
            echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>
