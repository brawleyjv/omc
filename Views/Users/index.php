<?php include __DIR__ . '/../header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="..//public/css/.">
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>
        <p>Update your profile and password here.</p>
        <form action="profile.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>" required>
            <label for="position">Position:</label>
            <input type="text" id="position" name="position" value="<?php echo $position; ?>" required>
            <label for="user_type">User Type:</label>
            <select id="user_type" name="user_type" required>
                <option value="admin" <?php if ($user_type == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="user" <?php if ($user_type == 'user') echo 'selected'; ?>>User</option>
            </select>
            <label for="date_of_hire">Date of Hire:</label>
            <input type="date" id="date_of_hire" name="date_of_hire" value="<?php echo $date_of_hire; ?>" required>
            <label for="password">Password (leave blank to keep current password):</label>
            <input type="password" id="password" name="password">
            <input type="submit" value="Update Profile">
        </form>
        <a href="../main.php" class="button">Return to Main</a>
    </div>
</body>
</html>
