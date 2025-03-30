<?php
require_once __DIR__  . '/Config.php';
require_once BASE_PATH . '/Models/Database.php';
require_once BASE_PATH . '/Controllers/VendorController.php';

$vendor = $_POST['vendor'];

$sql = "SELECT * FROM vendors WHERE Vendor = '$vendor'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo 'exists';
} else {
    echo 'not exists';
}

mysqli_close($conn);
?>
