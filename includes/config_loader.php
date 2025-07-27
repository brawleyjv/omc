<?php
/**
 * Universal Config Loader
 * This file helps locate and include config.php regardless of directory structure
 * Works with both local development and live server deployments
 */

// Prevent multiple inclusions
if (defined('CONFIG_LOADED')) {
    return;
}

function findConfigPath($currentDir) {
    // Common relative paths to try
    $paths = [
        $currentDir . '/config.php',                    // Same directory
        $currentDir . '/../config.php',                 // One level up
        $currentDir . '/../../config.php',              // Two levels up
        $currentDir . '/../../../config.php',           // Three levels up (for nested omc/omc)
        $currentDir . '/../../../../config.php',        // Four levels up
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // If none of the common paths work, search up the directory tree
    $searchDir = $currentDir;
    for ($i = 0; $i < 10; $i++) { // Limit search to prevent infinite loops
        $configPath = $searchDir . '/config.php';
        if (file_exists($configPath)) {
            return $configPath;
        }
        $parentDir = dirname($searchDir);
        if ($parentDir === $searchDir) {
            break; // Reached root directory
        }
        $searchDir = $parentDir;
    }
    
    return false;
}

// Find and include config.php
$configPath = findConfigPath(__DIR__);
if ($configPath === false) {
    die("Error: Could not locate config.php file. Please check your installation.");
}

require_once $configPath;
define('CONFIG_LOADED', true);
?>
