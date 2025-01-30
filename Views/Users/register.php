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
        <form action="../../public/Users/register.php" method="post"> <!-- Updated form action -->
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