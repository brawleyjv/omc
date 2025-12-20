<?php
include 'header.php';

// Retrieve company name and slogan from the database
include 'config.php';
$query = "SELECT company_name AS name, company_slogan AS slogan FROM omc_db.settings LIMIT 1";
$result = mysqli_query($conn, $query);
$company = mysqli_fetch_assoc($result);

// Store company name and slogan in session
$_SESSION['company_name'] = $company['name'];
$_SESSION['company_slogan'] = $company['slogan'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to <?php echo $_SESSION['company_name']; ?></h1>
        <p><?php echo $_SESSION['company_slogan']; ?></p>
        <p>You have successfully logged in.</p>
        <div class="button-container">
            <a href="customers/index.php" class="button">Customer Management</a>
            <a href="equipment/index.php" class="button">Equipment Management</a>
            <a href="materials/index.php" class="button">Material Management</a>
            <a href="projects/index.php" class="button">Project Management</a>
            <a href="Users/index.php" class="button">User Profile</a>
        </div>
    </div>
</body>
</html>
