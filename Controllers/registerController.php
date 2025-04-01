<?php
namespace Controllers;

require_once realpath(dirname(__FILE__) . '/../config.php'); // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';

use MyApp\Models\Database;

class RegisterController {
    private $database;

    public function __construct() {
        // Ensure constants are defined before using them
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_NAME')) {
            throw new \Exception('Database configuration constants are not defined in config.php');
        }

        // Retrieve database configuration
        $host = DB_HOST;
        $username = DB_USER; // Corrected constant name
        $password = DB_PASSWORD;
        $dbname = DB_NAME;

        $this->database = new Database($host, $username, $password, $dbname); // Instantiate the Database class with required arguments
    }

    public function registerUser($name, $phone, $position, $user_type, $date_of_hire, $password) {
        $conn = $this->database->getConnection(); // Get the database connection

        if (!$conn) {
            throw new \Exception("Database connection is null.");
        }

        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare('INSERT INTO users (name, phone, position, user_type, date_of_hire, password) VALUES (:name, :phone, :position, :user_type, :date_of_hire, :password)');
            $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, \PDO::PARAM_STR);
            $stmt->bindParam(':position', $position, \PDO::PARAM_STR);
            $stmt->bindParam(':user_type', $user_type, \PDO::PARAM_STR);
            $stmt->bindParam(':date_of_hire', $date_of_hire, \PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, \PDO::PARAM_STR);

            // Execute the query
            if ($stmt->execute()) {
                return true; // Registration successful
            } else {
                return false; // Registration failed
            }
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage()); // Log the error
            return false;
        }
    }
}
?>