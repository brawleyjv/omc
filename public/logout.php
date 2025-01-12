<?php
require_once '../Controllers/LogoutController.php';

use Controllers\LogoutController;

$logoutController = new LogoutController();
$logoutController->logout();
?>
