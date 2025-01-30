<?php
namespace MyApp\Controllers;

include_once '../Models/Database.php';
include_once '../Globals/Config.php'; // Corrected path

use MyApp\Models\Database;
use Globals\Config;

class UserController {
    private $db;

    public function __construct() {
        $this->db = new Database(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS); // Use Config constants
        $connection = $this->db->getConnection();
        // ...existing code using $connection...
    }
    // ...existing code...
}
?>
