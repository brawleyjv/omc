<?php
// Start output buffering to ensure all output is sent to the browser
ob_start();

// Include config.php for database credentials
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Models/Database.php'; // Include the Database class

use MyApp\Models\Database; // Ensure this is at the top, outside of any block or function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proceed with the update process
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Progress</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Update Progress</h1>
    <div id="progress">
<?php
function outputMessage($message, $type = 'info') {
    $class = $type === 'success' ? 'success' : ($type === 'error' ? 'error' : '');
    echo "<p class=\"$class\">$message</p>";
    ob_flush();
    flush();
}

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

$database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed

try {
    $repoOwner = 'brawleyjv';
    $repoName = 'omc';
    $branch = 'main';
    $localRoot = $_SERVER['DOCUMENT_ROOT'] . '/OMC';

    outputMessage("Fetching file list from GitHub...");
    $files = fetchFilesFromGitHub($repoOwner, $repoName, $branch);
    outputMessage("File list fetched successfully.", 'success');

    foreach ($files as $file) {
        $githubUrl = "https://raw.githubusercontent.com/$repoOwner/$repoName/$branch/" . $file['path'];
        $localPath = $localRoot . '/' . $file['path'];

        if (isFileNewerOnGitHub($githubUrl, $localPath)) {
            outputMessage("Downloading file: " . $file['path']);
            downloadFileFromGitHub($githubUrl, $localPath);
            outputMessage("Downloaded: $localPath", 'success');

            if (pathinfo($localPath, PATHINFO_EXTENSION) === 'sql') {
                outputMessage("Executing SQL file: $localPath");
                executeSqlFile($localPath, $connection);
                outputMessage("Executed SQL file: $localPath", 'success');
            }
        } else {
            outputMessage("Skipped (up-to-date): $localPath", 'info');
        }
    }

    outputMessage("Update process completed successfully.", 'success');
} catch (Exception $e) {
    outputMessage("Error: " . $e->getMessage(), 'error');
}
?>
    </div>
</body>
</html>
<?php
    ob_end_flush();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Update Confirmation</h1>
    <p class="warning">You are about to update the system. This process will:</p>
    <ul>
        <li>Fetch the latest files from the GitHub repository.</li>
        <li>Download and replace local files if they are outdated.</li>
        <li>Execute any SQL scripts included in the update.</li>
    </ul>
    <p>Please ensure you have backed up your system before proceeding.</p>
    <form method="POST">
        <button type="submit">Proceed with Update</button>
        <button type="button" onclick="window.location.href='/OMC';">Cancel</button>
    </form>
</body>
</html>
<?php
// End output buffering and flush all output
ob_end_flush();
?>