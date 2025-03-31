<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php'; // Ensure correct path to config.php
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

include BASE_PATH . '/Views/vendors/index.php'; // Corrected the path to the view
?>
