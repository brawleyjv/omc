<?php
namespace Controllers;

use Globals\Config;

class LoginController {
    private $conn;

    public function __construct() {
        $this->conn = new \mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function login($name) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE name = ?");
        if (!$stmt) {
            error_log("Prepared statement failed: " . $this->conn->error);
            echo "Prepared statement failed: " . $this->conn->error; // Debug output
            header("Location: home.php?error=An unexpected error occurred");
            exit();
        }

        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['username'] = $name;
                $stmt->close();
                header("Location: ../Public/main.php");
                exit();
            } else {
                $stmt->close();
                header("Location: ../Users/register.php?name=" . urlencode($name));
                exit();
            }
        } else {
            error_log("Database error: " . $this->conn->error);
            echo "Database error: " . $this->conn->error; // Debug output
            $stmt->close();
            header("Location: home.php?error=An unexpected error occurred");
            exit();
        }
    }
}
?>
