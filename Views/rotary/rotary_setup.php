<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    require_once realpath(dirname(__FILE__) . '/../../config.php'); // Include config if not already included
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotary Setup</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css"> <!-- Link to styles.css -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f9; }
        .container { max-width: 500px; width: 100%; padding: 20px; margin: 20px auto; border: 1px solid #ccc; border-radius: 8px; background: #ffffff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; color: #333; }
        label { font-weight: bold; display: block; margin-top: 15px; color: #555; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; text-align: center; }
        .button-container { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn.styled-btn { padding: 12px 20px; border-radius: 4px; text-decoration: none; text-align: center; display: inline-block; }
        .btn.styled-btn:first-child { background: #007bff; color: white; } /* Calculate Steps button */
        .btn.styled-btn:last-child { background: #dc3545; color: white; } /* Close button */
        .btn.styled-btn:last-child:hover { background: #c82333; } /* Close button hover effect */
        .result { margin-top: 20px; font-size: 18px; font-weight: bold; text-align: center; color: #28a745; }
    </style>
</head>
<body>
    <?php include realpath(dirname(__FILE__) . '/../header.php'); ?> <!-- Include header -->
    <div class="container">
        <h1 class="title">Rotary Setup</h1>
        <!-- Add content for Rotary Setup here -->
        <div class="container">
            <h2>Rotary Steps Calculator</h2>
            <form method="post">
                <label>Motor Steps Per Revolution:</label>
                <input type="number" name="motorSteps" value="200" required>
                
                <label>Microstepping Factor:</label>
                <input type="number" name="microSteps" value="16" required>
                
                <label>Roller Diameter (in inches):</label>
                <input type="number" step="0.01" name="rollerDiameter" value="2.0" required>
                
                <label>Workpiece Diameter (in inches):</label>
                <input type="number" step="0.01" name="workpieceDiameter" value="3.5" required>
                
                <div class="button-container">
                    <button type="submit" class="btn styled-btn">Calculate Steps</button> <!-- Styled button -->
                    <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn styled-btn">Close</a> <!-- Close button -->
                </div>
            </form>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                function calculateStepsPerRotation($motorSteps, $microSteps, $rollerDiameter, $workpieceDiameter) {
                    // Convert diameters to circumferences
                    $rollerCircumference = pi() * $rollerDiameter;
                    $workpieceCircumference = pi() * $workpieceDiameter;

                    // Calculate steps per rotation
                    $stepsPerRotation = ($motorSteps * $microSteps) * ($workpieceCircumference / $rollerCircumference);

                    return round($stepsPerRotation, 2);
                }

                // Get user input
                $motorSteps = $_POST["motorSteps"];
                $microSteps = $_POST["microSteps"];
                $rollerDiameter = $_POST["rollerDiameter"];
                $workpieceDiameter = $_POST["workpieceDiameter"];

                // Calculate result
                $calculatedSteps = calculateStepsPerRotation($motorSteps, $microSteps, $rollerDiameter, $workpieceDiameter);
                echo "<div class='result'>Recommended Steps per Rotation: $calculatedSteps</div>";
            }
            ?>
        </div>
    </div>
</body>
</html>
