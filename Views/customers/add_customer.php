<?php
require_once __DIR__ . '/../../config.php'; // Updated to use __DIR__
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Ensure styles.css is included -->
    <style>
        input[type="text"], input[type="email"], textarea {
            width: 100%; /* Standardize width */
            max-width: 400px; /* Optional: Limit maximum width */
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        textarea#notes {
            width: 100%; /* Utilize full width of the container */
            max-width: none; /* Remove max-width restriction */
            height: 150px; /* Make notes field larger */
        }
        label[for="notes"] {
            display: block; /* Ensure label is on its own line */
            text-align: center; /* Center the label */
            margin-bottom: 5px; /* Add spacing below the label */
        }
        .form-row {
            display: flex;
            align-items: center;
            justify-content: center; /* Center items horizontally */
            gap: 10px; /* Add spacing between fields */
        }
        .form-row label {
            flex: 0 0 auto; /* Prevent label from stretching */
        }
        .form-row input {
            flex: 1; /* Allow input to take remaining space */
        }
        .form-row .zip {
            max-width: 100px; /* Limit width of zip field */
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px; /* Add spacing between buttons */
            margin-top: 20px;
        }
        .btn.styled-btn.red {
            background-color: #DC3545; /* Red background */
            color: white; /* White text */
            padding: 10px 30px; /* Increase padding for a longer button */
            font-size: 16px; /* Ensure consistent font size */
            text-align: center; /* Center text in the button */
            border: none; /* Remove border */
        }
        .btn.styled-btn.red:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .btn.close-btn {
            background-color: #f44336; /* Red background */
            color: white; /* White text */
        }
        .btn.close-btn:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Updated to use BASE_PATH -->
    <div class="container">
        <h1 class="title">Add Customer</h1>
        <form action="<?php echo BASE_URL; ?>public/customers/add_customer.php" method="post"> <!-- Use BASE_URL -->
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" maxlength="100" required>

            <label for="project_name">Project Name:</label>
            <input type="text" id="project_name" name="project_name" maxlength="100" required>

            <fieldset>
                <legend>Address</legend>
                <label for="address">Street Address:</label>
                <input type="text" id="address" name="address" maxlength="200" placeholder="Street Address">

                <label for="city">City:</label>
                <input type="text" id="city" name="city" maxlength="100">

                <div class="form-row">
                    <label for="state">State:</label>
                    <input type="text" id="state" name="state" maxlength="50">

                    <label for="zip">Zip Code:</label>
                    <input type="text" id="zip" name="zip" maxlength="10" class="zip">
                </div>
            </fieldset>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" maxlength="15">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" maxlength="100">

            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" maxlength="500"></textarea>

            <div class="button-container">
                <button type="submit" class="btn styled-btn">Add Customer</button>
                <a href="<?php echo BASE_URL; ?>Views/customers/index.php" class="btn styled-btn red">Close</a>
            </div>
        </form>
    </div>
</body>
</html>