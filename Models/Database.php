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
    private $connection;

    public function __construct($host = DB_HOST, $user = DB_USER, $password = DB_PASSWORD, $dbname = DB_NAME) { // Use DB_PASSWORD
        $this->host = $host;
        $this->user = $user; // Ensure DB_USER is used for the username
        $this->password = $password;
        $this->dbname = $dbname;
        $this->connection = null; // Initialize connection as null
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->user, $this->password); // Use $this->user for the username
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage()); // Log the error for debugging
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
}
?>