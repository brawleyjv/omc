<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Include the configuration file
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Services/FileManager.php';

class UpdateController {
    private $fileManager;

    public function __construct() {
        $this->fileManager = new FileManager();
    }

    public function downloadZip() {
        error_log("UpdateController: downloadZip() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Download process started...</p>";
            ob_flush();
            flush();

            $zipUrl = "https://github.com/brawleyjv/omc/archive/refs/heads/main.zip";
            $savePath = $_SERVER['DOCUMENT_ROOT'] . '/main.zip'; // Correct save path

            // Step 1: Download the ZIP file
            error_log("UpdateController: Attempting to download file from $zipUrl to $savePath.");
            $this->fileManager->downloadFile($zipUrl, $savePath);
            echo "<p style='color: green; text-align: center;'>Download complete. File saved to: $savePath</p>"; // Updated confirmation message
            ob_flush();
            flush();

            // Add a "Back to Update" button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='/OMC/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Update</a>
                  </div>";
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

    public function backupDatabase() {
        error_log("UpdateController: backupDatabase() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Starting database backup process...</p>";
            ob_flush();
            flush();

            // Use credentials from config.php
            global $dbConfig;
            $databaseName = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];

            error_log("UpdateController: Using database credentials - DB: $databaseName, User: $username"); // Log credentials for debugging

            $backupFile = $this->fileManager->backupDatabase($databaseName, $username, $password);

            error_log("UpdateController: Database backup completed successfully. File saved to: $backupFile"); // Log success
            echo "<p style='color: green; text-align: center;'>Database backup completed successfully. File saved to: $backupFile</p>";
            ob_flush();
            flush();

            // Add a "Back to Update" button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='/OMC/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Update</a>
                  </div>";
            ob_flush();
            flush();
        } catch (Exception $e) {
            error_log("UpdateController: Error in backupDatabase(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }

    public function importDatabase() {
        error_log("UpdateController: importDatabase() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Starting database import process...</p>";
            ob_flush();
            flush();

            // Use credentials from config.php
            global $dbConfig;
            $host = $dbConfig['host'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];
            $database = $dbConfig['database'];

            // Find the newest .sql file in the root directory
            $directory = $_SERVER['DOCUMENT_ROOT'];
            $latestFile = null;
            $latestTime = 0;

            foreach (glob($directory . '/*.sql') as $file) {
                $fileTime = filemtime($file);
                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                    $latestFile = $file;
                }
            }

            if (!$latestFile) {
                throw new Exception("No .sql files found in the directory: $directory");
            }

            error_log("UpdateController: Found latest SQL file: $latestFile");

            require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Services/SqlImporter.php';
            $importer = new SqlImporter(); // No arguments needed
            $importer->import($latestFile);

            error_log("UpdateController: Database import completed successfully.");
            echo "<p style='color: green; text-align: center;'>Database import completed successfully. File: $latestFile</p>";
            ob_flush();
            flush();
        } catch (Exception $e) {
            error_log("UpdateController: Error in importDatabase(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }
}
?>
