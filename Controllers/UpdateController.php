<?php
namespace MyApp\Controllers;

require_once realpath(dirname(__FILE__) . '/../config.php'); // Include the configuration file
require_once BASE_PATH . '/Services/FileManager.php'; // Ensure FileManager.php is included
require_once BASE_PATH . '/Services/SqlImporter.php'; // Ensure SqlImporter.php is included

use MyApp\Services\FileManager;
use MyApp\Services\SqlImporter; // Correct namespace for SqlImporter

class UpdateController {
    private $fileManager;

    public function __construct() {
        $this->fileManager = new FileManager(); // Initialize FileManager
    }

    public function downloadZip() {
        error_log("UpdateController: downloadZip() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Download process started...</p>";
            ob_flush();
            flush();

            $zipUrl = "https://github.com/brawleyjv/omc/archive/refs/heads/main.zip";
            $savePath = BASE_PATH . 'main.zip'; // Correct save path

            // Step 1: Download the ZIP file
            error_log("UpdateController: Attempting to download file from $zipUrl to $savePath.");
            $this->fileManager->downloadFile($zipUrl, $savePath);
            echo "<p style='color: green; text-align: center;'>Download complete. File saved to: $savePath</p>"; // Updated confirmation message
            ob_flush();
            flush();

            // Add a "Back to Update" button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='" . BASE_URL . "/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Update</a>
                  </div>";
            ob_flush();
            flush();
        } catch (\Exception $e) {
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
            $databaseName = DB_NAME;
            $username = DB_USER;
            $password = DB_PASSWORD;

            if (empty($databaseName) || empty($username) || empty($password)) {
                error_log("One or more database credentials are missing in the config.");
                echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: One or more database credentials are missing in the config.</p>";
                ob_flush();
                flush();
                return;
            }

            error_log("UpdateController: Using database credentials - DB: $databaseName, User: $username"); // Log credentials for debugging

            $backupFile = $this->fileManager->backupDatabase($databaseName, $username, $password);

            error_log("UpdateController: Database backup completed successfully. File saved to: $backupFile"); // Log success
            echo "<p style='color: green; text-align: center;'>Database backup completed successfully. File saved to: $backupFile</p>";
            ob_flush();
            flush();

            // Add a "Back to Update" button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='" . BASE_URL . "/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Update</a>
                  </div>";
            ob_flush();
            flush();
        } catch (\Exception $e) {
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
            $pdo = new \PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD); // Use \PDO for global namespace
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // Use \PDO for global namespace

            $directory = BASE_PATH . 'public/db_backup/backup_files/';
            $latestFile = null;
            $latestTime = 0;

            foreach (glob($directory . '*.sql') as $file) {
                $fileTime = filemtime($file);
                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                    $latestFile = $file;
                }
            }

            if (!$latestFile) {
                throw new \Exception("No .sql files found in the directory: $directory");
            }

            error_log("UpdateController: Found latest SQL file: $latestFile");

            $importer = new SqlImporter($pdo); // Pass the PDO instance to SqlImporter
            $importer->import($latestFile);

            error_log("UpdateController: Database import completed successfully.");
            echo "<p style='color: green; text-align: center;'>Database import completed successfully. File: $latestFile</p>";
            ob_flush();
            flush();
        } catch (\Exception $e) {
            error_log("UpdateController: Error in importDatabase(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }

    public function updateDatabase() {
        error_log("UpdateController: updateDatabase() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Starting database update process...</p>";
            ob_flush();
            flush();

            // Add logic for updating the database here
            // For example, you can call the importDatabase method or execute specific SQL commands

            echo "<p style='color: green; text-align: center;'>Database update completed successfully.</p>";
            ob_flush();
            flush();
        } catch (\Exception $e) {
            error_log("UpdateController: Error in updateDatabase(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }

    public function manualBackup() {
        error_log("UpdateController: manualBackup() method called."); // Log method call
        try {
            echo "<p style='color: blue; text-align: center;'>Starting manual backup process...</p>";
            ob_flush();
            flush();

            // Use credentials from config.php
            $databaseName = DB_NAME;
            $username = DB_USER;
            $password = DB_PASSWORD;

            if (empty($databaseName) || empty($username) || empty($password)) {
                error_log("One or more database credentials are missing in the config.");
                echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: One or more database credentials are missing in the config.</p>";
                ob_flush();
                flush();
                return;
            }

            error_log("UpdateController: Using database credentials - DB: $databaseName, User: $username"); // Log credentials for debugging

            $backupFile = $this->fileManager->backupDatabase($databaseName, $username, $password);

            error_log("UpdateController: Manual backup completed successfully. File saved to: $backupFile");
            echo "<p style='color: green; text-align: center;'>Manual backup completed successfully. File saved to: $backupFile</p>";
            ob_flush();
            flush();

            // Add a "Back to Update" button
            echo "<div style='text-align: center; margin-top: 20px;'>
                    <a href='" . BASE_URL . "/Views/update.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Update</a>
                  </div>";
            ob_flush();
            flush();
        } catch (\Exception $e) {
            error_log("UpdateController: Error in manualBackup(): " . $e->getMessage());
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            ob_flush();
            flush();
        }
    }
}
?>
