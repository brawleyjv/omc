<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Estimate</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css">
</head>
<body>
    <h1>Add Estimate</h1>
    <form action="<?php echo BASE_URL; ?>public/Estimate/add_estimate.php" method="POST">
        <label for="project_id">Project ID:</label>
        <input type="text" id="project_id" name="project_id" required>
        
        <label for="estimate_data">Estimate Data:</label>
        <textarea id="estimate_data" name="estimate_data" required></textarea>
        
        <label for="user_id">User ID:</label>
        <input type="text" id="user_id" name="user_id" required>
        
        <button type="submit">Submit</button>
    </form>
</body>
</html>
