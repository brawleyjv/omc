<?php
namespace MyApp\Services;

use PDO;
use PDOException;

class SqlImporter {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo; // Store the PDO instance
    }

    public function import($filePath) {
        if (!file_exists($filePath)) {
            throw new \Exception("SQL file not found: $filePath");
        }

        try {
            $sql = file_get_contents($filePath);
            if ($sql === false) {
                throw new \Exception("Failed to read SQL file: $filePath");
            }

            $this->pdo->exec($sql); // Execute the SQL commands
        } catch (PDOException $e) {
            throw new \Exception("Database import failed: " . $e->getMessage());
        }
    }
}
?>
