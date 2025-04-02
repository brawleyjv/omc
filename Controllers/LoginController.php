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
        $query = "SELECT * FROM users WHERE name = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) { // Verify the hashed password
                return $user; // Return user data on successful login
            }
        }
        return false; // Return false if login fails
    }
}
?>
