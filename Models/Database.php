<?php

namespace MyApp\Models;

require_once realpath(dirname(__FILE__) . '/../config.php');

use PDO;
use PDOException;

class Database {
    private PDO $connection;

    public function __construct($host = DB_HOST, $user = DB_USER, $password = DB_PASSWORD, $dbname = DB_NAME) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $this->connection = new PDO($dsn, $user, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getConnection(): PDO {
        return $this->connection; // Return the PDO connection
    }
}
?>