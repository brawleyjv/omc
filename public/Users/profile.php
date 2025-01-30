<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $user_type = $_POST['user_type'];
    $date_of_hire = $_POST['date_of_hire'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "UPDATE users SET phone=:phone, position=:position, user_type=:user_type, date_of_hire=:date_of_hire, password=:password WHERE name=:name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':position', $position);
    $stmt->bindParam(':user_type', $user_type);
    $stmt->bindParam(':date_of_hire', $date_of_hire);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':name', $name);

    if ($stmt->execute()) {
        echo "<script>
                alert('Profile updated successfully');
                window.location.href = '../main.php';
              </script>";
    } else {
        echo "Error: " . $stmt->errorInfo()[2]; // Use errorInfo() to get the error message
    }

    $conn = null; // Close the connection
}
?>
