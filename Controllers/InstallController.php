<?php
namespace Controllers;

require_once __DIR__ . '/../Globals/Config.php'; // Include the configuration file

use Globals\Config;

class InstallController {
    private $conn;

    public function __construct() {
        $this->conn = new \mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function install($company_name, $company_slogan, $db_name, $db_host, $db_username, $db_password) {
        // ...existing code...
    }

    public function update($company_name, $company_slogan) {
        // ...existing code...
    }

    // Other methods as needed
}
?>
