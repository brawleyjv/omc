<?php
// Start output buffering to ensure all output is sent to the browser
ob_start();

// Include config.php for database credentials
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/OMC/Models/Database.php'; // Include the Database class

use MyApp\Models\Database; // Ensure this is at the top, outside of any block or function

// Function to load the exclusion list from an .ini file
function loadExclusionList($filePath) {
    if (!file_exists($filePath)) {
        return []; // Return an empty array if the file doesn't exist
    }
    $exclusions = parse_ini_file($filePath, true);
    return $exclusions['exclude'] ?? []; // Return the 'exclude' section or an empty array
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_exclusions'])) {
    $newExclusions = $_POST['exclusions'] ?? '';
    $exclusionFile = $_SERVER['DOCUMENT_ROOT'] . '/OMC/exclusions.ini';

    // Prepare the content for the .ini file
    $iniContent = "[exclude]\n" . trim($newExclusions);

    // Attempt to save the exclusions
    if (file_put_contents($exclusionFile, $iniContent) === false) {
        die("Failed to save exclusions to the .ini file. Please check file permissions.");
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['list_exclusions'])) {
    // Reload exclusions from the .ini file
    $exclusionFile = $_SERVER['DOCUMENT_ROOT'] . '/OMC/exclusions.ini';
    $currentExclusions = file_exists($exclusionFile) ? parse_ini_file($exclusionFile, true)['exclude'] ?? [] : [];
    $exclusionText = implode("\n", $currentExclusions);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_exclusions'])) {
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

        function isExcluded($filePath, $exclusions) {
            $filePath = strtolower($filePath); // Convert file path to lowercase
            foreach ($exclusions as $exclusion) {
                $exclusion = strtolower($exclusion); // Convert exclusion to lowercase
                // Check if the exclusion is a folder (ends with a slash)
                if (str_ends_with($exclusion, '/')) {
                    if (strpos($filePath, rtrim($exclusion, '/')) === 0) {
                        return true; // File is inside the excluded folder
                    }
                } elseif (fnmatch($exclusion, $filePath)) {
                    return true; // File matches the exclusion pattern
                }
            }
            return false;
        }

        $database = new Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // Ensure required arguments are passed

        try {
            $repoOwner = 'brawleyjv';
            $repoName = 'omc';
            $branch = 'main';
            $localRoot = $_SERVER['DOCUMENT_ROOT'] . '/OMC';
            $exclusionFile = $localRoot . '/exclusions.ini'; // Path to the exclusion list file
            $exclusions = loadExclusionList($exclusionFile);

            outputMessage("Fetching file list from GitHub...");
            $files = fetchFilesFromGitHub($repoOwner, $repoName, $branch);
            outputMessage("File list fetched successfully.", 'success');

            foreach ($files as $file) {
                $filePath = $file['path'];

                // Skip files or folders in the exclusion list
                if (isExcluded($filePath, $exclusions)) {
                    outputMessage("Excluded: $filePath", 'info');
                    continue;
                }

                $githubUrl = "https://raw.githubusercontent.com/$repoOwner/$repoName/$branch/" . $filePath;
                $localPath = $localRoot . '/' . $filePath;

                if (isFileNewerOnGitHub($githubUrl, $localPath)) {
                    outputMessage("Downloading file: " . $filePath);
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

// Load exclusions for display
$exclusionFile = $_SERVER['DOCUMENT_ROOT'] . '/OMC/exclusions.ini';
$currentExclusions = file_exists($exclusionFile) ? parse_ini_file($exclusionFile, true)['exclude'] ?? [] : [];
$exclusionText = implode("\n", $currentExclusions);
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
        textarea { width: 100%; height: 150px; }
        .instructions { font-size: 0.9em; color: #555; margin-top: 10px; }
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
    <h2>Edit Exclusions</h2>
    <form method="POST">
        <textarea name="exclusions"><?php echo htmlspecialchars($exclusionText); ?></textarea>
        <button type="submit" name="save_exclusions">Save Exclusions</button>
        <button type="submit" name="list_exclusions">List Exclusions</button>
    </form>
    <div class="instructions">
        <p><strong>Instructions:</strong></p>
        <ul>
            <li>To exclude a specific file, add its name or relative path, for example: <em>file.php</em> or <em>folder/file.php</em></li>
            <li>To exclude an entire folder, add the folder name with a trailing slash, for example: <em>folder_name/</em></li>
            <li>To exclude files with a specific extension, use a wildcard, for example: <em>*.log</em></li>
        </ul>
    </div>
</body>
</html>
<?php
// End output buffering and flush all output
ob_end_flush();
?>