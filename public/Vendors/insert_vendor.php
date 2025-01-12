<?php
include '../config.php';

$vendor = $_POST['vendor'];
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$web_address = isset($_POST['web_address']) ? $_POST['web_address'] : '';
$mailing_address = isset($_POST['mailing_address']) ? $_POST['mailing_address'] : '';
$email_address = isset($_POST['email_address']) ? $_POST['email_address'] : '';

$sql = "INSERT INTO vendors (Vendor, Phone, Web_Address, Mailing_Address, Email_Address) VALUES ('$vendor', '$phone', '$web_address', '$mailing_address', '$email_address')";

if (mysqli_query($conn, $sql)) {
    echo "Vendor added successfully";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>
