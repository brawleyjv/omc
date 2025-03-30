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
        try {
            $zipUrl = "https://github.com/brawleyjv/omc/archive/refs/heads/main.zip";
            $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/main.zip';
            $extractPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC';

            $this->fileManager->downloadFile($zipUrl, $savePath);
            $this->fileManager->extractZip($savePath, $extractPath);

            echo "<p style='color: green; text-align: center;'>Update package downloaded and extracted successfully to: $extractPath</p>";
        } catch (Exception $e) {
            echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
        }
    }

    public function updateDatabase() {
        try {
            $sqlUrl = "https://raw.githubusercontent.com/brawleyjv/omc/main/omc_db.sql";
            $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/omc_db.sql';
            $backupPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/backup_' . date('Y-m-d_H-i-s') . '.sql';

            $this->dbManager->backupDatabase($backupPath);
            echo "<p style='color: green; text-align: center;'>Database backup created successfully: $backupPath</p>";

            $this->fileManager->downloadFile($sqlUrl, $savePath);
            $this->dbManager->importDatabase($savePath);

            echo "<p style='color: green; text-align: center;'>Database updated successfully using: $savePath</p>";
        } catch (Exception $e) {
            echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
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
