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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="../../public/css/styles.css"> <!-- Corrected path -->
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var toggleButton = document.getElementById("togglePassword");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleButton.textContent = "Hide Password";
            } else {
                passwordField.type = "password";
                toggleButton.textContent = "Show Password";
            }
        }
    </script>
</head>
<body>
    <?php include '../../views/header.php'; // Ensure this path is correct ?>
    <div class="container">
        <h1>User Registration</h1>
        <img src="../../public/images/login-image.png" alt="Registration Image" class="login-image"> <!-- Add your image here -->
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="register.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="" required> <!-- Ensure field is blank -->
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="" required> <!-- Ensure field is blank -->
            <label for="position">Position:</label>
            <input type="text" id="position" name="position" value="" required> <!-- Ensure field is blank -->
            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            <label for="date_of_hire">Date of Hire:</label>
            <input type="date" id="date_of_hire" name="date_of_hire" value="" required> <!-- Ensure field is blank -->
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="" required> <!-- Ensure field is blank -->
            <button type="button" id="togglePassword" onclick="togglePasswordVisibility()">Show Password</button> <!-- Toggle button -->
            <input type="submit" value="Register" class="btn styled-btn"> <!-- Updated button style -->
        </form>
    </div>
</body>
</html>
