<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <style>
        .center-title {
            text-align: center;
            margin-top: -10px; /* Adjust the margin to bring the title back down by 100px */
        }
        .form-container {
            max-width: 100%;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .button-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
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
            display: inline-block;
        }
        .btn.styled-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include '../../views/header.php'; ?>
    <h1 class="center-title">Add New Material</h1>
    <div class="button-container">
        <button class="btn styled-btn" onclick="document.getElementById('add-material-form').submit();">Add Material</button>
    </div>
    <div class="form-container">
        <form id="add-material-form" action="../../public/materials/add_material.php" method="post">
            <div class="form-group">
                <label for="material_name">Material Name</label>
                <input type="text" id="material_name" name="material_name" required>
            </div>
            <div class="form-group">
                <label for="length">Length</label>
                <input type="number" step="0.01" id="length" name="length">
            </div>
            <div class="form-group">
                <label for="width">Width</label>
                <input type="number" step="0.01" id="width" name="width">
            </div>
            <div class="form-group">
                <label for="thickness">Thickness</label>
                <input type="number" step="0.01" id="thickness" name="thickness">
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price">
            </div>
            <div class="form-group">
                <label for="quantity_on_hand">Quantity on Hand</label>
                <input type="number" id="quantity_on_hand" name="quantity_on_hand">
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <input type="text" id="type" name="type">
            </div>
            <div class="form-group">
                <label for="vendor">Vendor</label>
                <input type="text" id="vendor" name="vendor">
            </div>
            <div class="form-group">
                <label for="item_no">Item No</label>
                <input type="text" id="item_no" name="item_no">
            </div>
            <div class="form-group">
                <label for="item_url">Item URL</label>
                <input type="url" id="item_url" name="item_url">
            </div>
            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="url" id="image_url" name="image_url">
            </div>
        </form>
    </div>
</body>
</html>