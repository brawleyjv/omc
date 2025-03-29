<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Updated to use document root
require_once BASE_PATH . '/Views/header.php'; // Ensure BASE_PATH is used correctly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scale Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css"> <!-- Ensure BASE_URL is used correctly -->
    <style>
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .btn.styled-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            text-align: center;
        }
        .help-dialog {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 10;
            width: 200px;
        }
        .form-group:hover .help-dialog,
        .button-container button:hover .help-dialog {
            display: block;
        }
    </style>
    <script>
        function convertToImperial(value, unit) {
            if (unit === 'metric') {
                return value * 0.0393701; // Convert millimeters to inches
            }
            return value;
        }

        function calculateScale() {
            const materialThickness = parseFloat(document.getElementById('material-thickness').value);
            const materialUnit = document.getElementById('material-unit').value;
            const drawingThickness = parseFloat(document.getElementById('drawing-thickness').value);
            const drawingUnit = document.getElementById('drawing-unit').value;

            const materialThicknessImperial = convertToImperial(materialThickness, materialUnit);
            const drawingThicknessImperial = convertToImperial(drawingThickness, drawingUnit);

            if (!isNaN(materialThicknessImperial) && !isNaN(drawingThicknessImperial)) {
                if (Math.abs(materialThicknessImperial - drawingThicknessImperial) < 0.0001) {
                    document.getElementById('result').innerText = 'No scaling is required.';
                } else {
                    const scalePercentage = (materialThicknessImperial / drawingThicknessImperial) * 100;
                    if (scalePercentage >= 100) {
                        document.getElementById('result').innerText = `Increase the percentage scale of the project by ${scalePercentage.toFixed(2)}%.`;
                    } else {
                        document.getElementById('result').innerText = `Reduce the percentage scale of the project by ${scalePercentage.toFixed(2)}%.`;
                    }
                }
            } else {
                document.getElementById('result').innerText = 'Please enter valid numbers for both thicknesses.';
            }
        }

        function clearFields() {
            document.getElementById('material-thickness').value = '';
            document.getElementById('material-unit').value = 'imperial';
            document.getElementById('drawing-thickness').value = '';
            document.getElementById('drawing-unit').value = 'imperial';
            document.getElementById('result').innerText = '';
        }
    </script>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- Ensure BASE_PATH is used correctly -->
    <div class="container">
        <h1 class="title">Scale Project</h1>
        <div class="form-group">
            <label for="material-thickness">Material Thickness:</label>
            <input type="number" step="0.01" id="material-thickness" name="material-thickness" required>
            <select id="material-unit" name="material-unit">
                <option value="imperial">Inches</option>
                <option value="metric">Millimeters</option>
            </select>
            <div class="help-dialog">Enter the thickness of the material you are using.</div>
        </div>
        <div class="form-group">
            <label for="drawing-thickness">Project Drawing Thickness:</label>
            <input type="number" step="0.01" id="drawing-thickness" name="drawing-thickness" required>
            <select id="drawing-unit" name="drawing-unit">
                <option value="imperial">Inches</option>
                <option value="metric">Millimeters</option>
            </select>
            <div class="help-dialog">Enter the thickness specified in the project drawing.</div>
        </div>
        <div class="button-container">
            <button class="btn styled-btn" onclick="calculateScale()">Calculate
                <div class="help-dialog">Click to calculate the scaling percentage.</div>
            </button>
            <button class="btn styled-btn" onclick="clearFields()">Clear
                <div class="help-dialog">Click to clear all input fields.</div>
            </button>
            <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>views/main.php'">Main Menu
                <div class="help-dialog">Click to return to the main menu.</div>
            </button>
        </div>
        <div class="result" id="result"></div>
    </div>
</body>
</html>
