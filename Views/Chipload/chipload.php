<?php
require_once realpath(dirname(__FILE__) . '/../../config.php'); // Updated to use realpath
require_once BASE_PATH . '/Views/header.php'; // BASE_PATH is assumed to be defined in config.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chipload</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/styles.css"> <!-- Updated to use BASE_PATH -->
    <style>
        .calculator-container {
            margin-top: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .calculator-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .calculator-container label {
            display: block;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .calculator-container input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            max-width: 300px;
        }
        .calculator-container button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }
        .calculator-container button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/Views/header.php'; ?> <!-- BASE_PATH ensures correct path -->
    <div class="container">
        <h1 class="title">Chipload Calculator</h1>
        <p>
            1. Wood:<br>
            Softwoods: 100–300 IPM, depending on the bit size and material density.<br>
            Hardwoods: 75–200 IPM, with slower speeds for denser woods like oak or maple.<br>
            Plywood/MDF: 100–250 IPM, as these materials are less dense but can dull bits faster.<br><br>
            2. Acrylic:<br>
            General Range: 75–300 IPM.<br>
            Smaller bits (e.g., 1/8") work better at the lower end (75–100 IPM), while larger bits (e.g., 1/2") can handle higher speeds (200–300 IPM).<br>
            Ensure proper cooling to avoid melting the acrylic.<br><br>
            3. Aluminum:<br>
            General Range: 10–50 IPM for smaller bits and light cuts.<br>
            For larger bits or deeper cuts, speeds can increase to 50–150 IPM, depending on the material grade and tool type.
        </p>
        <div class="calculator-container">
            <h2>Calculate Feed Rate and RPM</h2>
            <form id="chipload-form">
                <label for="material">Material:</label>
                <select id="material" name="material" required>
                    <option value="wood">Wood</option>
                    <option value="acrylic">Acrylic</option>
                    <option value="aluminum">Aluminum</option>
                </select>
                <label for="bit_size">Bit Size:</label>
                <select id="bit_size" name="bit_size" required>
                    <option value="0.125">0.125"</option>
                    <option value="0.25">0.25"</option>
                </select>
                <label for="flute_count">Bit Flute Count:</label>
                <input type="number" id="flute_count" name="flute_count" required>
                <label for="max_rpm">Maximum RPM:</label>
                <input type="number" id="max_rpm" name="max_rpm" required>
                <button type="button" onclick="calculateFeedRate()">Calculate</button>
            </form>
            <div class="result" id="result"></div>
        </div>
        <button class="btn styled-btn" onclick="window.location.href='<?php echo BASE_URL; ?>Views/main.php'">Main Menu
        </div>
    <script>
        function calculateFeedRate() {
            const material = document.getElementById('material').value;
            const bitSize = document.getElementById('bit_size').value;
            const fluteCount = document.getElementById('flute_count').value;
            const maxRpm = document.getElementById('max_rpm').value;

            let chipload;

            if (material && bitSize && fluteCount && maxRpm) {
                if (material === 'wood') {
                    chipload = bitSize === '0.125' ? 0.004 : 0.0075;
                } else if (material === 'acrylic') {
                    chipload = bitSize === '0.125' ? 0.003 : 0.006;
                } else if (material === 'aluminum') {
                    chipload = bitSize === '0.125' ? 0.0015 : 0.003;
                }

                const rpm = maxRpm;
                const feedRate = (fluteCount * chipload * rpm).toFixed(2);

                document.getElementById('result').innerText = `Recommended RPM: ${rpm} RPM\nRecommended Feed Rate: ${feedRate} IPM`;
            } else {
                document.getElementById('result').innerText = 'Please fill in all fields.';
            }
        }
    </script>
</body>
</html>
