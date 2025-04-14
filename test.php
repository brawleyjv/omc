<?php
require_once realpath(dirname(__FILE__) . '/config.php'); // Ensure config.php is included

echo "BASE_PATH: " . BASE_PATH . "<br>";
echo "Config File Path: " . realpath(dirname(__FILE__) . '/config.php');
?>