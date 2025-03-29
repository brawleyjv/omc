<?php
function fetchFilesFromGitHub($repoOwner, $repoName, $branch) {
    $url = "https://api.github.com/repos/$repoOwner/$repoName/git/trees/$branch?recursive=1";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github.v3+json',
        'User-Agent: YourAppName',
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die("Failed to fetch file list from GitHub.");
    }

    $data = json_decode($response, true);
    if (isset($data['tree'])) {
        return array_filter($data['tree'], fn($item) => $item['type'] === 'blob');
    }

    die("Failed to parse file list from GitHub.");
}

function isFileNewerOnGitHub($url, $localPath) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github.v3.raw',
        'User-Agent: YourAppName',
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        die("Failed to fetch file metadata from GitHub.");
    }

    // Extract the Last-Modified header
    if (preg_match('/Last-Modified: (.+)/i', $response, $matches)) {
        $githubLastModified = strtotime(trim($matches[1]));
        if (file_exists($localPath)) {
            $localLastModified = filemtime($localPath);
            return $githubLastModified > $localLastModified;
        }
        return true; // Local file doesn't exist, so GitHub file is "newer"
    }

    return true; // If no Last-Modified header, assume the file needs to be updated
}

function downloadFileFromGitHub($url, $savePath) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github.v3.raw',
        'User-Agent: YourAppName',
    ]);
    $data = curl_exec($ch);
    curl_close($ch);

    if ($data === false) {
        die("Failed to download file from GitHub.");
    }

    // Ensure the directory exists
    $directory = dirname($savePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); // Create directories recursively
    }

    file_put_contents($savePath, $data);
}

function executeSqlFile($filePath, $connection) {
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        die("Failed to read SQL file.");
    }

    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $connection->exec($statement);
        }
    }
}

// Include config.php for database credentials
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Models/Database.php'; // Include the Database class

use MyApp\Models\Database; // Add this if Database is in a namespace

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed

try {
    $repoOwner = 'brawleyjv';
    $repoName = 'omc';
    $branch = 'main';
    $localRoot = $_SERVER['DOCUMENT_ROOT'] . '/OMC';

    // Fetch all files from the GitHub repository
    $files = fetchFilesFromGitHub($repoOwner, $repoName, $branch);

    foreach ($files as $file) {
        $githubUrl = "https://raw.githubusercontent.com/$repoOwner/$repoName/$branch/" . $file['path'];
        $localPath = $localRoot . '/' . $file['path'];

        if (isFileNewerOnGitHub($githubUrl, $localPath)) {
            downloadFileFromGitHub($githubUrl, $localPath);
            echo "Downloaded: $localPath\n";

            // Execute SQL file if applicable
            if (pathinfo($localPath, PATHINFO_EXTENSION) === 'sql') {
                executeSqlFile($localPath, $connection);
                echo "Executed SQL file: $localPath\n";
            }
        } else {
            echo "Skipped (up-to-date): $localPath\n";
        }
    }

    echo "Update process completed successfully.";
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>