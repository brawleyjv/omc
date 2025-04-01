<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST['company_name'];
    $company_slogan = $_POST['company_slogan'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_host = $_POST['db_host'] ?? '';
    $db_username = $_POST['db_username'] ?? '';
    $db_password = $_POST['db_password'] ?? '';

    if (isset($_POST['update'])) {
        // Establish the database connection
        include realpath(dirname(__FILE__) . '/config.php');
    } else {
        $config_content = "<?php
\$servername = \"$db_host\";
\$username = \"$db_username\";
\$password = \"$db_password\";
\$dbname = \"$db_name\";

// Create connection
\$conn = new mysqli(\$servername, \$username, \$password, \$dbname);

// Check connection
if (\$conn->connect_error) {
    die(\"Connection failed: \" . \$conn->connect_error);
}
?>";

        file_put_contents('config.php', $config_content);

        // Establish the database connection
        include 'config.php';
    }

    // Check if the settings table exists
    $check_table_query = "SHOW TABLES LIKE 'settings'";
    $table_exists = mysqli_query($conn, $check_table_query);

    if (mysqli_num_rows($table_exists) == 0) {
        // Create the settings table if it doesn't exist
        $create_table_query = "CREATE TABLE settings (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(255) NOT NULL,
            company_slogan VARCHAR(255)
        )";
        mysqli_query($conn, $create_table_query);
    }

    // Check if there is an existing record in the settings table
    $check_record_query = "SELECT * FROM settings LIMIT 1";
    $record_exists = mysqli_query($conn, $check_record_query);

    if (mysqli_num_rows($record_exists) > 0) {
        // Update the existing record
        $update_query = "UPDATE settings SET company_name='$company_name', company_slogan='$company_slogan' WHERE id=1";
        mysqli_query($conn, $update_query);
    } else {
        // Insert a new record
        $insert_query = "INSERT INTO settings (company_name, company_slogan) VALUES ('$company_name', '$company_slogan')";
        mysqli_query($conn, $insert_query);
    }

    session_start();
    $_SESSION['company_name'] = $company_name;
    $_SESSION['company_slogan'] = $company_slogan;

    if (isset($_POST['update'])) {
        header("Location: main.php");
    } else {
        header("Location: Users/register.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install OMC Web Application</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Install OMC Web Application</h1>
        <form action="install.php" method="post">
            <label for="company_name">Company Name:</label>
            <input type="text" id="company_name" name="company_name" required>
            <label for="company_slogan">Company Slogan:</label>
            <input type="text" id="company_slogan" name="company_slogan">
            <label for="db_name">Database Name:</label>
            <input type="text" id="db_name" name="db_name">
            <label for="db_host">Database Host:</label>
            <input type="text" id="db_host" name="db_host">
            <label for="db_username">Database Username:</label>
            <input type="text" id="db_username" name="db_username">
            <label for="db_password">Database Password:</label>
            <input type="password" id="db_password" name="db_password">
            <input type="submit" value="Install">
            <input type="submit" name="update" value="Update">
        </form>
    </div>
</body>
</html>
