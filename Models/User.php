<?php
namespace MyApp\Models;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php

use PDO; // Import PDO at the top
use MyApp\Models\Database;

class User {
    private $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
        if (!$this->conn instanceof PDO) { // Simplified \PDO to PDO
            throw new \Exception("Invalid database connection.");
        }
    }

    public function login($username, $password) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        try {
            // Debugging log
            error_log("Attempting to log in user: $username");

            $sql = "SELECT * FROM users WHERE name = :username";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Debugging log
                error_log("User found: " . print_r($user, true));

                // Check if the password column is hashed
                if (!password_get_info($user['password'])['algo']) {
                    error_log("Password in database is not hashed. Please hash passwords using password_hash.");
                    return false;
                }

                if (password_verify($password, $user['password'])) {
                    // Debugging log
                    error_log("Password verified for user: $username");
                    return $user; // Return user data on successful login
                } else {
                    // Debugging log
                    error_log("Password verification failed for user: $username");
                }
            } else {
                // Debugging log
                error_log("No user found with username: $username");
            }
        } catch (\Exception $e) {
            // Debugging log
            error_log("Error in login method: " . $e->getMessage());
        }

        return false; // Return false if login fails
    }

    public function isNameTaken($name) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE name = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();

            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (\Exception $e) {
            error_log("Error in isNameTaken method: " . $e->getMessage());
            return false;
        }
    }

    public function createUser($name, $phone, $position, $user_type, $date_of_hire, $password) {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $sql = "INSERT INTO users (name, phone, position, user_type, date_of_hire, password) 
                    VALUES (:name, :phone, :position, :user_type, :date_of_hire, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt->bindParam(':position', $position, PDO::PARAM_STR);
            $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
            $stmt->bindParam(':date_of_hire', $date_of_hire, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR); // Use hashed password

            if ($stmt->execute()) {
                return true;
            } else {
                error_log("Error creating user: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (\Exception $e) {
            error_log("Exception in createUser method: " . $e->getMessage());
            return false;
        }
    }

    public function hashExistingPasswords() {
        if (!$this->conn) {
            throw new \Exception("Database connection is null.");
        }
        try {
            $sql = "SELECT id, password FROM users";
            $stmt = $this->conn->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                if (!password_get_info($user['password'])['algo']) { // Check if password is not hashed
                    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
                    $updateSql = "UPDATE users SET password = :password WHERE id = :id";
                    $updateStmt = $this->conn->prepare($updateSql);
                    $updateStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                    $updateStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
                    $updateStmt->execute();
                }
            }
        } catch (\Exception $e) {
            error_log("Error in hashExistingPasswords method: " . $e->getMessage());
        }
    }
}
?>
