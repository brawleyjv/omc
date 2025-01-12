<?php
use Controllers\InstallController;

require_once '../Globals/Config.php';
require_once '../Controllers/InstallController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST['company_name'];
    $company_slogan = $_POST['company_slogan'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_host = $_POST['db_host'] ?? '';
    $db_username = $_POST['db_username'] ?? '';
    $db_password = $_POST['db_password'] ?? '';

    $installController = new InstallController();

    if (isset($_POST['update'])) {
        $installController->update($company_name, $company_slogan);
        header("Location: main.php");
    } else {
        $installController->install($company_name, $company_slogan, $db_name, $db_host, $db_username, $db_password);
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
    <link rel="stylesheet" href="..//public/css/.">
</head>
<body>
    <?php include '../Views/header.php'; ?> <!-- Ensure the header file is included -->
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
