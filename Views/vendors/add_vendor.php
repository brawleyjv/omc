<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vendor</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <script>
        function submitForm(event) {
            event.preventDefault();
            var form = document.querySelector('form');
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../public/Vendors/add_vendor.php', true);
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
            xhrInsert.open('POST', '../../public/Vendors/insert_vendor.php', true);
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
            margin-bottom: 20px;
        }
        .btn.styled-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <div class="container">
        <div class="button-container">
            <input type="submit" value="Submit" class="btn styled-btn" onclick="submitForm(event)">
            <a href="../../views/vendors/index.php" class="btn styled-btn">Close</a>
        </div>
        <h1 class="title">Add Vendor</h1>
        <form>
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
        </form>
    </div>
</body>
</html>
