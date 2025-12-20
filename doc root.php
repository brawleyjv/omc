<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Output the document root
echo "<h3>Testing Document Root:</h3>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Verify if a specific file exists in the document root
$expectedFilePath = $_SERVER['DOCUMENT_ROOT'] . '/clickandbuilds/OzarkMade/omc/config.php'; // Adjust path as needed

if (file_exists($expectedFilePath)) {
    echo "<p>The file <strong>config.php</strong> exists at: $expectedFilePath</p>";
} else {
    echo "<p>The file <strong>config.php</strong> does NOT exist at: $expectedFilePath</p>";
}

// Debugging information for further troubleshooting
echo "<h3>Debugging Info:</h3>";
echo "<p><strong>Current Script Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
?>