<?php
namespace MyApp\Controllers;

require_once realpath(dirname(__FILE__) . '/../config.php');
require_once realpath(dirname(__FILE__) . '/../Models/Database.php');
require_once realpath(dirname(__FILE__) . '/../Models/User.php');

use MyApp\Models\Database;
use MyApp\Models\User;

class UserController {
    private $user;

    public function __construct($database) {
        $this->user = new User($database); // Pass Database instance to User model
    }

    public function register($data) {
        $name = $data['name'];
        $phone = $data['phone'];
        $position = $data['position'];
        $user_type = $data['user_type'];
        $date_of_hire = $data['date_of_hire'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($this->user->isNameTaken($name)) {
            header("Location: " . BASE_URL . "Views/Users/register.php?error=" . urlencode("Name already taken."));
            exit();
        }

        $this->user->createUser($name, $phone, $position, $user_type, $date_of_hire, $password);
        header("Location: " . BASE_URL . "Views/Users/login.php");
        exit();
    }

    public function hashPasswordsForExistingUsers() {
        $this->user->hashExistingPasswords(); // Call the method to hash existing passwords
    }
}
?>
