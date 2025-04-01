<?php
require_once realpath(dirname(__FILE__) . '/../config.php'); // Include the configuration file

class SqlImporter {
    private $connection;

    public function __construct() {
        global $dbConfig; // Use global database configuration
        $host = $dbConfig['host'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $database = $dbConfig['database'];

        $this->connection = new mysqli($host, $username, $password, $database);

        if ($this->connection->connect_error) {
            throw new Exception("Database connection failed: " . $this->connection->connect_error);
        }
    }

    public function import($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found: $filePath");
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new Exception("Failed to read the SQL file.");
        }

        // Split the SQL file into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        $this->connection->begin_transaction();
        try {
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    if (!$this->connection->query($statement)) {
                        throw new Exception("Error executing SQL: " . $this->connection->error . " | SQL: $statement");
                    }
                }
            }
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    public function __destruct() {
        $this->connection->close();
    }
}
?>
