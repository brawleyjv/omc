<?php
class FileManager {
    public function downloadFile($url, $savePath) {
        $fileData = file_get_contents($url);
        if ($fileData === false) {
            throw new Exception("Failed to download the file from $url.");
        }

        if (file_put_contents($savePath, $fileData) === false) {
            throw new Exception("Failed to save the file to $savePath.");
        }
    }

    public function extractZip($zipPath, $extractPath) {
        if (!class_exists('ZipArchive')) {
            throw new Exception("The ZipArchive class is not available. Please enable the zip extension in your PHP configuration.");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            unlink($zipPath); // Delete the zip file after extraction
        } else {
            throw new Exception("Failed to open the zip file for extraction.");
        }
    }
}
?>
