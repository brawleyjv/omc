<?php
namespace Controllers;

class LogoutController {
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "public/login.php"); // Ensure this path is correct
        exit(); // Ensure no further code is executed
    }
}
?>
