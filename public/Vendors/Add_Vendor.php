<?php include '../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vendor</title>
    <link rel="stylesheet" href="..//public/css/.">
    <script>
        function submitForm(event) {
            event.preventDefault();
            var form = document.querySelector('form');
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_vendor.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    if (xhr.responseText === 'exists') {
                        if (confirm('Vendor already exists. Do you want to continue?')) {
                            insertVendor(formData);
                        }
                    } else {
                        insertVendor(formData);
                    }
                } else {
                    alert('Failed to check vendor');
                }
            };
            xhr.send(formData);
        }

        function insertVendor(formData) {
            var xhrInsert = new XMLHttpRequest();
            xhrInsert.open('POST', 'insert_vendor.php', true);
            xhrInsert.onload = function () {
                if (xhrInsert.status === 200) {
                    alert('Vendor added successfully');
                    window.opener.location.reload();
                    window.close();
                } else {
                    alert('Failed to add vendor');
                }
            };
            xhrInsert.send(formData);
        }
    </script>
    <style>
        .button-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Vendor</h1>
        <form onsubmit="submitForm(event)">
            <label for="vendor">Vendor:</label>
            <input type="text" id="vendor" name="vendor" required><br>
            
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone"><br>
            
            <label for="web_address">Web Address:</label>
            <input type="text" id="web_address" name="web_address"><br>
            
            <label for="mailing_address">Mailing Address:</label>
            <input type="text" id="mailing_address" name="mailing_address"><br>
            
            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address" size="40"><br>
            
            <div class="button-container">
                <input type="submit" value="Submit">
                <button type="button" onclick="window.close()">Close</button>
                <p>Click close to exit adding a new vendor</p>
            </div>
        </form>
    </div>
</body>
</html>
