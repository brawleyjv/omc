<?php
// Enable error reporting for debugging (only in development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dynamically set BASE_PATH
define('BASE_PATH', realpath(__DIR__) . '/');

// Dynamically set BASE_URL
define('BASE_URL', ($_SERVER['HTTP_HOST'] === 'localhost') 
	? 'http://localhost/OMC/' 
	: 'https://www.app.ozarkmadecrafts.com/');

// Database connection constants
define('DB_HOST', 'db5017536213.hosting-data.io');
define('DB_NAME', 'omc_db');
define('DB_USER', 'dbu2170183');
define('DB_PASSWORD', 'GmmRsd2xZnajuT!');

// Log defined constants for debugging
error_log("DB_HOST: " . DB_HOST);
error_log("DB_NAME: " . DB_NAME);
error_log("DB_USER: " . DB_USER);
error_log("BASE_URL: " . BASE_URL);
?>