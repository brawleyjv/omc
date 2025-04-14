<?php

namespace MyApp\Models;

require_once realpath(dirname(__FILE__) . '/../config.php');

use PDO;
use PDOException;

class Database {
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $connection; // Holds the PDO instance

    public function __construct($host = DB_HOST, $user = DB_USER, $password = DB_PASSWORD, $dbname = DB_NAME) {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->connection = null; // Initialize connection as null
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->user, $this->password); // Create a PDO instance
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage()); // Log the error for debugging
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        if ($this->connection === null) { // Check if the connection is null
            $this->connect(); // Call the connect method to initialize the connection
        }
        if ($this->connection === null) { // Ensure connection is initialized
            throw new PDOException("Database connection is not established.");
        }
        return $this->connection; // Return the PDO instance
    }

    public function getPdo(): PDO {
        return $this->getConnection();
    }
}
?>