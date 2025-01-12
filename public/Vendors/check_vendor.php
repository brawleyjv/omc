<?php
include '../config.php';

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
