<?php
class DatabaseManager {
    public function backupDatabase($backupPath) {
        // ...existing backup logic from update.php...
    }

    public function dropAllTables() {
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
            while ($row = $result->fetch_row()) {
                $table = $row[0];
                if ($table !== 'users') { // Skip the 'users' table
                    if (!$connection->query("DROP TABLE `$table`")) {
                        throw new Exception("Failed to drop table `$table`: " . $connection->error);
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
    }

    public function importDatabase($filePath) {
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
    }

    public function restoreDatabase($filePath) {
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
    }
}
?>
