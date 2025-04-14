<?php
namespace MyApp\Controllers;

require_once realpath(dirname(__FILE__) . '/../config.php');
require_once BASE_PATH . 'Models/Database.php';
require_once BASE_PATH . 'Models/User.php';

use PDO;
use MyApp\Models\User;

class UserController {
    private PDO $db; // Declare the $db property with type PDO

    public function __construct(PDO $pdo) { // Accept a PDO instance directly
        $this->db = $pdo; // Initialize the $db property
    }

    public function registerUser($name, $phone, $position, $user_type, $date_of_hire, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
        $query = "INSERT INTO users (name, phone, position, user_type, date_of_hire, password) 
                  VALUES (:name, :phone, :position, :user_type, :date_of_hire, :password)";
        $stmt = $this->db->prepare($query); // Use the initialized $db property
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':position', $position, PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
        $stmt->bindParam(':date_of_hire', $date_of_hire, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR); // Use the hashed password
        return $stmt->execute(); // Execute the query
    }

    public function register($data) {
        $name = $data['name'];
        $phone = $data['phone'];
        $position = $data['position'];
        $user_type = $data['user_type'];
        $date_of_hire = $data['date_of_hire'];
        $password = $data['password'];

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password

        $query = "INSERT INTO users (name, phone, position, user_type, date_of_hire, password) 
                  VALUES (:name, :phone, :position, :user_type, :date_of_hire, :password)";
        $stmt = $this->db->prepare($query); // Use the initialized $db property
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':position', $position, PDO::PARAM_STR);
        $stmt->bindParam(':user_type', $user_type, PDO::PARAM_STR);
        $stmt->bindParam(':date_of_hire', $date_of_hire, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        return $stmt->execute(); // Execute the query
    }

    public function hashPasswordsForExistingUsers() {
        $query = "SELECT id, password FROM users WHERE password NOT LIKE '$2y$%'";
        $stmt = $this->db->prepare($query); // Use the initialized $db property
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hashedPassword = password_hash($row['password'], PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :hashedPassword WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery); // Use the initialized $db property
            $updateStmt->bindParam(':hashedPassword', $hashedPassword, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
            $updateStmt->execute(); // Execute the update query
        }
    }
}
?>
