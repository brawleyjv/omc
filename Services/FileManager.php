<?php
ini_set('memory_limit', '1024M'); // Increase memory limit to 1GB
ini_set('max_execution_time', '300'); // Increase execution time to 300 seconds (5 minutes)

class FileManager {
    public function downloadFile($url, $savePath) {
        // Ensure the save path is adjusted to point to C:\xampp\htdocs\
        $savePath = str_replace('/OMC/', '/', $savePath); // Adjust path to root directory
        $fileData = file_get_contents($url);
        if ($fileData === false) {
            throw new Exception("Failed to download the file from $url.");
        }

        if (file_put_contents($savePath, $fileData) === false) {
            throw new Exception("Failed to save the file to $savePath.");
        }
    }
}
?>
