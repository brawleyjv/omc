<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add BOM</title>
    <link rel="stylesheet" href="/OMC/public/css/styles.css">
    <style>
        .container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .column {
            flex: 1;
        }
        .material-search {
            margin-bottom: 20px;
        }
        .material-list {
            list-style-type: none;
            padding: 0;
        }
        .material-list li {
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .material-list li:hover {
            background-color: #f0f0f0;
        }
        .material-entry {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .form-group label {
            width: 150px;
            margin-right: 10px;
        }
        .form-group input {
            flex: 1;
            padding: 5px;
            font-size: 14px;
        }
        .remove-button {
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 3px;
            margin-top: 10px;
            display: block;
            width: 100%;
            text-align: center;
        }
    </style>
    <script>
        function addMaterial(materialId) {
            fetch(`../../public/bom/get_material_details.php?material_id=${materialId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(error => { throw new Error(error.error); });
                    }
                    return response.json();
                })
                .then(material => {
                    const materialContainer = document.getElementById('material-container');
                    const materialTemplate = document.getElementById('material-template').content.cloneNode(true);
                    const materialEntries = document.querySelectorAll('.material-entry').length;
                    materialTemplate.querySelector('.material-name').id = `material_name_${materialEntries}`;
                    materialTemplate.querySelector('.material-id').id = `material_id_${materialEntries}`;
                    materialTemplate.querySelector('.material-name').value = material.material_name;
                    materialTemplate.querySelector('.material-id').value = materialId;
                    
                    // Add length, width, thickness, and quantity fields
                    const materialDetails = document.createElement('div');
                    materialDetails.id = `material-details-${materialEntries}`;
                    materialDetails.innerHTML = `
                        <div class="form-group">
                            <label for="material_id_${materialEntries}">Material ID:</label>
                            <input type="text" id="material_id_${materialEntries}" name="material_id[]" class="material-id" value="${materialId}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="length_${materialEntries}">Length:</label>
                            <input type="number" step="0.01" id="length_${materialEntries}" name="length[]" class="length" value="${material.length}" required>
                        </div>
                        <div class="form-group">
                            <label for="width_${materialEntries}">Width:</label>
                            <input type="number" step="0.01" id="width_${materialEntries}" name="width[]" class="width" value="${material.width}" required>
                        </div>
                        <div class="form-group">
                            <label for="thickness_${materialEntries}">Thickness:</label>
                            <input type="number" step="0.01" id="thickness_${materialEntries}" name="thickness[]" class="thickness" value="${material.thickness}" required>
                        </div>
                        <div class="form-group">
                            <label for="quantity_${materialEntries}">Quantity:</label>
                            <input type="number" id="quantity_${materialEntries}" name="quantity[]" class="quantity" required>
                        </div>
                        <button type="button" class="remove-button" onclick="removeMaterial(${materialEntries})">Remove</button>
                    `;
                    materialTemplate.querySelector('.material-entry').appendChild(materialDetails);
                    materialContainer.appendChild(materialTemplate);
                })
                .catch(error => {
                    console.error('There was a problem with fetching material details:', error);
                    alert('An error occurred while fetching material details: ' + error.message);
                });
        }

        function removeMaterial(index) {
            const materialEntry = document.getElementById(`material_name_${index}`).closest('.material-entry');
            materialEntry.remove();
        }

        function fetchMaterials() {
            const searchQuery = document.getElementById('material-search').value;
            fetch(`../../public/bom/search_materials.php?query=${searchQuery}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(error => { throw new Error(error.error); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    const materialList = document.getElementById('material-list');
                    materialList.innerHTML = '';
                    data.forEach(material => {
                        const listItem = document.createElement('li');
                        listItem.innerHTML = `
                            <strong>${material.material_name}</strong><br>
                            Type: ${material.type}<br>
                            Length: ${material.length}, Width: ${material.width}, Thickness: ${material.thickness}<br>
                            ID: ${material.id}
                        `;
                        listItem.onclick = () => addMaterial(material.id);
                        materialList.appendChild(listItem);
                    });
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    alert('An error occurred while fetching materials: ' + error.message);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('project_id');
            const projectName = urlParams.get('project_name');
            const customerName = urlParams.get('customer_name');

            if (!projectId || !projectName || !customerName) {
                alert('Project ID, Project Name, and Customer Name are required.');
                window.location.href = '../../Views/projects/add_project.php';
            }
        });
    </script>
</head>
<body>
    <?php include '../../Views/header.php'; ?>
    <h1 class="title">Add Bill of Materials</h1>
    <div class="container">
        <div class="column">
            <div class="material-search">
                <h2>Search Materials</h2>
                <input type="text" id="material-search" oninput="fetchMaterials()" placeholder="Search for material types...">
                <ul id="material-list" class="material-list"></ul>
            </div>
        </div>
        <div class="column">
            <form action="../../public/bom/add_bom.php" method="post">
                <?php
                $project_id = isset($_GET['project_id']) ? htmlspecialchars($_GET['project_id']) : '';
                $project_name = isset($_GET['project_name']) ? htmlspecialchars($_GET['project_name']) : '';
                $customer_name = isset($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : '';
                ?>
                <div class="form-group">
                    <label for="project_id">Project ID:</label>
                    <input type="text" id="project_id" name="project_id" value="<?php echo $project_id; ?>" required readonly>
                </div>
                <div class="form-group">
                    <label for="project_name">Project Name:</label>
                    <input type="text" id="project_name" name="project_name" value="<?php echo $project_name; ?>" required readonly>
                </div>
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?php echo $customer_name; ?>" required readonly>
                </div>
                <div id="material-container"></div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <template id="material-template">
        <div class="material-entry">
            <div class="form-group">
                <label for="material_name_${document.querySelectorAll('.material-entry').length}">Material Name:</label>
                <input type="text" id="material_name_${document.querySelectorAll('.material-entry').length}" name="material_name[]" class="material-name" readonly required>
                <input type="hidden" id="material_id_${document.querySelectorAll('.material-entry').length}" name="material_id[]" class="material-id">
            </div>
            <div id="material-details-${document.querySelectorAll('.material-entry').length}" class="material-details"></div>
        </div>
    </template>
</body>
</html>
