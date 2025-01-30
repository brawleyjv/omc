<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Feet Calculator</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <h1 class="title">Board Feet Calculator</h1>
        <form method="post" action="">
            <label for="length">Length (inches):</label>
            <input type="number" id="length" name="length" required>
            <label for="width">Width (inches):</label>
            <input type="number" id="width" name="width" required>
            <label for="thickness">Thickness (inches):</label>
            <input type="number" id="thickness" name="thickness" required>
            <button type="submit" class="btn styled-btn">Calculate</button>
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $length = $_POST['length'];
            $width = $_POST['width'];
            $thickness = $_POST['thickness'];
            $boardFeet = ($length * $width * $thickness) / 144;
            echo "<p>Board Feet: " . number_format($boardFeet, 2) . "</p>";
        }
        ?>
    </div>
</body>
</html>
