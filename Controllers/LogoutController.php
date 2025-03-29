<?php
namespace MyApp\Controllers;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php

class LogoutController {
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "Views/login.php");
        exit();
    }
}
?>
