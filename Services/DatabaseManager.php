<?php
class DatabaseManager {
    public function backupDatabase($backupPath) {
        try {
            $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Full path to mysqldump
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPassword = ''; // Replace with your MySQL root password
            $dbName = 'omc_db'; // Replace with your database name

            $command = "\"$mysqldumpPath\" -h $dbHost -u $dbUser --password=$dbPassword $dbName > \"$backupPath\"";
            error_log("DatabaseManager: Executing backup command: $command"); // Log the command for debugging

            $output = [];
            $returnVar = null;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                // Log the output and return code for debugging
                error_log("DatabaseManager: mysqldump failed with return code $returnVar");
                error_log("DatabaseManager: Command output: " . implode("\n", $output));
                throw new Exception("Failed to create database backup. Command: $command");
            }

            error_log("DatabaseManager: Backup created successfully at $backupPath");
        } catch (Exception $e) {
            error_log("DatabaseManager: Error in backupDatabase(): " . $e->getMessage()); // Log the error
            throw $e; // Re-throw the exception to propagate it
        }
    }

    public function dropAllTables() {
        try {
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($connection->connect_error) {
                throw new Exception("Database connection failed: " . $connection->connect_error);
            }

            // Disable foreign key checks to allow dropping tables with constraints
            if (!$connection->query("SET FOREIGN_KEY_CHECKS = 0")) {
                throw new Exception("Failed to disable foreign key checks: " . $connection->error);
            }

            // Get all table names in the database
            $result = $connection->query("SHOW TABLES");
            if ($result) {
                // Define allowed table prefixes/patterns for security
                $allowedTablePrefixes = ['bom', 'customers', 'estimates', 'materials', 'projects', 'settings', 'setup', 'vendors'];
                
                while ($row = $result->fetch_row()) {
                    $table = $row[0];
                    if ($table !== 'users') { // Skip the 'users' table
                        // Validate table name against allowed patterns
                        $isValidTable = false;
                        foreach ($allowedTablePrefixes as $prefix) {
                            if (strpos($table, $prefix) === 0) {
                                $isValidTable = true;
                                break;
                            }
                        }
                        
                        if ($isValidTable && preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
                            if (!$connection->query("DROP TABLE `$table`")) {
                                throw new Exception("Failed to drop table `$table`: " . $connection->error);
                            }
                        }
                    }
                }
            } else {
                throw new Exception("Failed to retrieve table list: " . $connection->error);
            }

            // Re-enable foreign key checks
            if (!$connection->query("SET FOREIGN_KEY_CHECKS = 1")) {
                throw new Exception("Failed to re-enable foreign key checks: " . $connection->error);
            }

            $connection->close();
        } catch (Exception $e) {
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            throw $e; // Re-throw the exception to propagate it
        }
    }

    public function importDatabase($filePath) {
        try {
            $this->dropAllTables(); // Drop all tables except 'users'

            $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($connection->connect_error) {
                throw new Exception("Database connection failed: " . $connection->connect_error);
            }

            $sql = file_get_contents($filePath);
            if ($sql === false) {
                throw new Exception("Failed to read the SQL file.");
            }

            // Remove any SQL statements related to the 'users' table
            $sql = preg_replace('/CREATE TABLE `users`.*?;|INSERT INTO `users`.*?;|DROP TABLE IF EXISTS `users`.*?;/is', '', $sql);

            // Execute the SQL commands
            if ($connection->multi_query($sql)) {
                do {
                    if ($result = $connection->store_result()) {
                        $result->free();
                    }
                } while ($connection->more_results() && $connection->next_result());
            } else {
                throw new Exception("Error executing SQL: " . $connection->error);
            }

            $connection->close();
        } catch (Exception $e) {
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            throw $e; // Re-throw the exception to propagate it
        }
    }

    public function restoreDatabase($filePath) {
        try {
            $this->dropAllTables(); // Drop all tables except 'users'

            $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            if ($connection->connect_error) {
                throw new Exception("Database connection failed: " . $connection->connect_error);
            }

            $sql = file_get_contents($filePath);
            if ($sql === false) {
                throw new Exception("Failed to read the backup file.");
            }

            // Remove any SQL statements related to the 'users' table
            $sql = preg_replace('/CREATE TABLE `users`.*?;|INSERT INTO `users`.*?;|DROP TABLE IF EXISTS `users`.*?;/is', '', $sql);

            // Execute the SQL commands
            if ($connection->multi_query($sql)) {
                do {
                    if ($result = $connection->store_result()) {
                        $result->free();
                    }
                } while ($connection->more_results() && $connection->next_result());
            } else {
                throw new Exception("Error executing SQL: " . $connection->error);
            }

            $connection->close();
        } catch (Exception $e) {
            echo "<p style='color: red; font-weight: bold; text-align: center;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            throw $e; // Re-throw the exception to propagate it
        }
    }
}
?>
