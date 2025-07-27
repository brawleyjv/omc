<?php
namespace MyApp\Controllers;

require_once realpath(dirname(__FILE__) . '/../config.php'); // Include config.php
require_once BASE_PATH . 'Models/Database.php'; // Use BASE_PATH for consistent paths
require_once BASE_PATH . 'Models/User.php';

use PDO;

class LoginController {
    private PDO $db; // Declare the $db property with type PDO

    public function __construct(PDO $pdo) { // Accept a PDO instance directly
        $this->db = $pdo; // Initialize the $db property
    }

    public function login($username, $password) {
        try {
            error_log("LoginController: Attempting login for username: " . $username);
            
            $query = "SELECT * FROM users WHERE name = :username LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            error_log("LoginController: Query executed, rows found: " . $stmt->rowCount());

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("LoginController: User found: " . $user['name']);
                error_log("LoginController: Stored password hash: " . substr($user['password'], 0, 20) . "...");
                
                if (password_verify($password, $user['password'])) {
                    error_log("LoginController: Password verification successful");
                    return $user; // Return user data on successful login
                } else {
                    error_log("LoginController: Password verification failed");
                    // Check if password might be stored as plain text (legacy)
                    if ($password === $user['password']) {
                        error_log("LoginController: Plain text password match found - security issue!");
                        return $user;
                    }
                }
            } else {
                error_log("LoginController: No user found with username: " . $username);
            }
            
            return false; // Return false if login fails
        } catch (Exception $e) {
            error_log("LoginController: Exception during login: " . $e->getMessage());
            return false;
        }
    }
}
?>
