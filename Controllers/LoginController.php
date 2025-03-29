<?php
namespace MyApp\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/omc/config.php'; // Corrected path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Models/User.php';

use MyApp\Models\Database;
use MyApp\Models\User;

class LoginController {
    private $user;

    public function __construct($database) {
        $this->user = new User($database); // Pass Database instance to User model
    }

    public function login($username, $password) {
        return $this->user->login($username, $password); // Return user data or false
    }
}
?>
