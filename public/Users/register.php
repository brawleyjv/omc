<?php
include '../../Globals/config.php'; // Corrected path

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $user_type = $_POST['user_type'];
    $date_of_hire = $_POST['date_of_hire'];
    $password = $_POST['password']; // Store password as plain text

    // Check for duplicate name
    $check_sql = "SELECT * FROM users WHERE name='$name'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $error_message = "The name '$name' is already taken. Please choose a different name.";
    } else {
        $sql = "INSERT INTO users (name, phone, position, user_type, date_of_hire, password) VALUES ('$name', '$phone', '$position', '$user_type', '$date_of_hire', '$password')";

        if ($conn->query($sql) === TRUE) {
            header("Location: ../login.php"); // Redirect to login.php after successful registration
            exit();
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>

