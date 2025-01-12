<?php
namespace Controllers;

class LogoutController {
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: ../public/login.php"); // Corrected path to login.php
        exit(); // Ensure no further code is executed
    }
}
?>
