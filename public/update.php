<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Views/header.php'; // Include the header
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Include the database configuration

function backupDatabase($backupPath) {
    try {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($connection->connect_error) {
            throw new Exception("Database connection failed: " . $connection->connect_error);
        }

        // Get all tables in the database
        $tables = [];
        $result = $connection->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
        } else {
            throw new Exception("Failed to retrieve database tables: " . $connection->error);
        }

        $sqlBackup = "";
        foreach ($tables as $table) {
            // Get CREATE TABLE statement
            $createTableResult = $connection->query("SHOW CREATE TABLE `$table`");
            if ($createTableResult) {
                $row = $createTableResult->fetch_assoc();
                $sqlBackup .= $row['Create Table'] . ";\n\n";
            } else {
                throw new Exception("Failed to retrieve CREATE TABLE statement for $table: " . $connection->error);
            }

            // Get table data
            $tableDataResult = $connection->query("SELECT * FROM `$table`");
            if ($tableDataResult) {
                while ($row = $tableDataResult->fetch_assoc()) {
                    $columns = array_map(fn($col) => "`$col`", array_keys($row));
                    $values = array_map(fn($val) => $connection->real_escape_string($val), array_values($row));
                    $sqlBackup .= "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES ('" . implode("', '", $values) . "');\n";
                }
            } else {
                throw new Exception("Failed to retrieve data for $table: " . $connection->error);
            }

            $sqlBackup .= "\n";
        }

        // Save the backup to a file
        if (file_put_contents($backupPath, $sqlBackup) === false) {
            throw new Exception("Failed to save the database backup to $backupPath.");
        }

        // Keep only the three most recent backups
        $backupFiles = glob($_SERVER['DOCUMENT_ROOT'] . '/OMC/backup_*.sql');
        if (count($backupFiles) > 3) {
            // Sort files by modification time, oldest first
            usort($backupFiles, fn($a, $b) => filemtime($a) <=> filemtime($b));
            // Delete the oldest files, keeping only the three most recent
            while (count($backupFiles) > 3) {
                $oldestFile = array_shift($backupFiles);
                unlink($oldestFile);
            }
        }

        $connection->close();
        return true;
    } catch (Exception $e) {
        throw new Exception("Database backup failed: " . $e->getMessage());
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'download_zip') {
    $zipUrl = "https://github.com/brawleyjv/omc/archive/refs/heads/main.zip"; // GitHub zip file URL
    $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/main.zip'; // Path to save the zip file
    $extractPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC'; // Path to extract the files

    try {
        // Download the zip file
        $zipData = file_get_contents($zipUrl);
        if ($zipData === false) {
            throw new Exception("Failed to download the zip file.");
        }

        // Save the zip file to the specified path
        if (file_put_contents($savePath, $zipData) === false) {
            throw new Exception("Failed to save the zip file to $savePath.");
        }

        // Decompress the zip file
        $zip = new ZipArchive();
        if ($zip->open($savePath) === true) {
            $zip->extractTo($extractPath); // Extract files to the specified folder
            $zip->close();

            // Delete the zip file after extraction
            unlink($savePath);

            echo "<p style='color: green; text-align: center;'>Update package downloaded and extracted successfully to: $extractPath</p>";
        } else {
            throw new Exception("Failed to open the zip file for extraction.");
        }
    } catch (Exception $e) {
        echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'update_database') {
    $sqlUrl = "https://raw.githubusercontent.com/brawleyjv/omc/main/omc_db.sql"; // GitHub SQL file URL
    $savePath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/omc_db.sql'; // Path to save the SQL file
    $backupPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/backup_' . date('Y-m-d_H-i-s') . '.sql'; // Path to save the backup

    try {
        // Create a backup of the database
        backupDatabase($backupPath);
        echo "<p style='color: green; text-align: center;'>Database backup created successfully: $backupPath</p>";

        // Download the SQL file
        $sqlData = file_get_contents($sqlUrl);
        if ($sqlData === false) {
            throw new Exception("Failed to download the SQL file.");
        }

        // Save the SQL file to the specified path
        if (file_put_contents($savePath, $sqlData) === false) {
            throw new Exception("Failed to save the SQL file to $savePath.");
        }

        // Import the SQL file into the database
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($connection->connect_error) {
            throw new Exception("Database connection failed: " . $connection->connect_error);
        }

        $sql = file_get_contents($savePath);
        if ($sql === false) {
            throw new Exception("Failed to read the SQL file.");
        }

        // Execute the SQL commands
        if ($connection->multi_query($sql)) {
            do {
                // Flush results to process multiple queries
                if ($result = $connection->store_result()) {
                    $result->free();
                }
            } while ($connection->more_results() && $connection->next_result());
        } else {
            throw new Exception("Error executing SQL: " . $connection->error);
        }

        // Close the database connection
        $connection->close();

        // Delete the SQL file after importing
        unlink($savePath);

        echo "<p style='color: green; text-align: center;'>Database updated successfully using: $savePath</p>";
    } catch (Exception $e) {
        echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'restore_backup') {
    if (isset($_POST['backup_file']) && !empty($_POST['backup_file'])) {
        $backupFile = $_SERVER['DOCUMENT_ROOT'] . '/OMC/' . basename($_POST['backup_file']); // Ensure only valid filenames are used

        try {
            if (!file_exists($backupFile)) {
                throw new Exception("The selected backup file does not exist.");
            }

            // Read the SQL file
            $sql = file_get_contents($backupFile);
            if ($sql === false) {
                throw new Exception("Failed to read the backup file.");
            }

            // Restore the database
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($connection->connect_error) {
                throw new Exception("Database connection failed: " . $connection->connect_error);
            }

            // Execute the SQL commands
            if ($connection->multi_query($sql)) {
                do {
                    // Flush results to process multiple queries
                    if ($result = $connection->store_result()) {
                        $result->free();
                    }
                } while ($connection->more_results() && $connection->next_result());
            } else {
                throw new Exception("Error executing SQL: " . $connection->error);
            }

            // Close the database connection
            $connection->close();

            echo "<p style='color: green; text-align: center;'>Database restored successfully from: $backupFile</p>";
        } catch (Exception $e) {
            echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>No backup file selected for restoration.</p>";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'manual_backup') {
    $backupPath = $_SERVER['DOCUMENT_ROOT'] . '/OMC/backup_' . date('Y-m-d_H-i-s') . '.sql'; // Path to save the backup

    try {
        // Create a manual backup of the database
        backupDatabase($backupPath);
        echo "<p style='color: green; text-align: center;'>Manual database backup created successfully: $backupPath</p>";
    } catch (Exception $e) {
        echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
    }
}
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
        <p>Use the buttons below to update, restore, or manually back up the system.</p>
        <p><strong>Instructions:</strong></p>
        <ul>
            <li><strong>Download and Extract Update Package:</strong> Downloads the latest update package from the repository, extracts its contents into the system folder, and replaces the existing files.</li>
            <li><strong>Update Database:</strong> Creates a backup of the current database, downloads the latest database update file, and applies it to the system.</li>
            <li><strong>Restore Database:</strong> Allows you to restore the database from a previously created backup file.</li>
            <li><strong>Manual Backup:</strong> Creates a manual backup of the current database, which can be restored later if needed.</li>
        </ul>
        <div class="button-container">
            <a href="?action=download_zip" class="btn styled-btn">Download and Extract Update Package</a>
            <a href="?action=update_database" class="btn styled-btn">Update Database</a>
            <form action="?action=restore_backup" method="post" style="display: inline;">
                <select name="backup_file" class="btn styled-btn">
                    <?php
                    // List all backup files in the OMC directory
                    $backupFiles = glob($_SERVER['DOCUMENT_ROOT'] . '/OMC/backup_*.sql');
                    foreach ($backupFiles as $file) {
                        $fileName = basename($file);
                        echo "<option value=\"$fileName\">$fileName</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn styled-btn">Restore Backup</button>
            </form>
            <a href="?action=manual_backup" class="btn styled-btn">Manual Backup</a>
            <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn cancel-btn">Cancel</a>
        </div>
    </div>
</body>
</html>