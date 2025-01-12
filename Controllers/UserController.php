<?php
namespace MyApp\Controllers;

include_once '../Models/Database.php';
include_once '../Config/Globals.php';

use MyApp\Models\Database;
use MyApp\Config\Globals;

class UserController {
    private $db;

    public function __construct() {
        $this->db = new Database('localhost', 'database_name', 'username', 'password');
        $connection = $this->db->getConnection();
        echo Globals\GLOBAL_VAR_1;
        // ...existing code using $connection...
    }
    // ...existing code...
}
?>
