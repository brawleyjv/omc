<?php
require_once realpath(dirname(__FILE__) .'/../config.php'); // Include the configuration file

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

    public function backupDatabase($databaseName, $username, $password) {
        $backupFile = BASE_PATH . $databaseName . '_backup_' . date('Y-m-d_H-i-s') . '.sql';        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump'; // Full path to mysqldump
        $command = "\"$mysqldumpPath\" -u$username --password=$password $databaseName > \"$backupFile\"";

        error_log("FileManager: Executing command: $command"); // Log the command for debugging

        $output = [];
        $returnVar = null;

        // Use proc_open to handle timeout
        $descriptors = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
        ini_set('max_execution_time', 0); // Remove time limit for this script

        $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process)) {
            $startTime = time();
            $timeout = 300; // 5 minutes
            $fileCompleted = false;

            // Monitor the process
            while (true) {
                $status = proc_get_status($process);
                if (!$status['running']) {
                    break; // Process completed
                }

                // Check if the backup file exists and is being written
                if (file_exists($backupFile)) {
                    $currentSize = filesize($backupFile);
                    error_log("FileManager: Backup file size: $currentSize bytes.");
                    
                    // Check if the file size stops growing (indicating completion)
                    clearstatcache(); // Clear file status cache
                    sleep(2); // Wait for 2 seconds
                    if ($currentSize === filesize($backupFile) && $currentSize > 0) {
                        $fileCompleted = true;
                        error_log("FileManager: Backup file appears to be complete.");
                        break;
                    }
                } else {
                    error_log("FileManager: Backup file does not exist yet.");
                }

                // Periodically reset the PHP execution timeout
                if (time() - $startTime > $timeout) {
                    set_time_limit(300); // Reset the execution time limit
                    $startTime = time(); // Reset the start time
                    error_log("FileManager: Resetting execution time limit to prevent timeout.");
                }

                usleep(500000); // Sleep for 0.5 seconds
            }

            // Ensure all pipes are closed
            fclose($pipes[1]);
            fclose($pipes[2]);

            // Explicitly terminate the process if it is still running
            $status = proc_get_status($process);
            if ($status['running']) {
                proc_terminate($process);
                error_log("FileManager: mysqldump process forcefully terminated.");
            }

            $returnVar = $status['exitcode'];
            proc_close($process);

            // Check if the file was completed
            if (!$fileCompleted) {
                error_log("FileManager: mysqldump process did not exit cleanly, but the backup file was written.");
            }

            if ($returnVar !== 0) {
                error_log("FileManager: mysqldump failed. Check the backup file or stderr for details.");
                throw new Exception("mysqldump failed with exit code $returnVar.");
            }

            error_log("FileManager: mysqldump completed successfully.");
        } else {
            throw new Exception("Failed to start mysqldump process.");
        }

        return $backupFile;
    }
}
?>
