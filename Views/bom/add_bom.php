<?php
require_once realpath(dirname(__FILE__) . '/../../Config.php'); // Updated to use realpath
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add BOM - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-brand">
                <div class="header-brand-text">
                    <h1>Add Bill of Materials</h1>
                    <p>Create and manage project material requirements</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="<?php echo BASE_URL; ?>Views/main.php" class="nav-link">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="nav-link">Projects</a>
                <a href="<?php echo BASE_URL; ?>Views/materials/list_materials.php" class="nav-link">Materials</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <h1 class="page-title">Create Bill of Materials</h1>
                <div class="page-actions">
                    <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-ghost">
                        <span class="icon">📋</span>
                        All Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- BOM Form -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Material Search -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Material Search</h2>
                    <p class="card-subtitle">Find and add materials to your BOM</p>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="material-search" class="form-label">Search Materials</label>
                        <input type="text" id="material-search" class="form-control" 
                               placeholder="Enter material name..." 
                               onkeyup="searchMaterials()">
                    </div>
                    <div id="material-list" class="mt-4">
                        <!-- Materials will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Selected Materials -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Selected Materials</h2>
                    <p class="card-subtitle">Materials added to this BOM</p>
                </div>
                <div class="card-body">
                    <div id="material-container">
                        <!-- Selected materials will appear here -->
                    </div>
                    <div class="form-actions mt-4">
                        <button type="button" onclick="addNewProject()" class="btn btn-secondary">
                            <span class="icon">📋</span>
                            Add New Project
                        </button>
                    </div>
                </div>
            </div>
        </div>
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
        <!-- BOM Submission Form -->
        <div class="card mt-6">
            <div class="card-header">
                <h2 class="card-title">BOM Information</h2>
                <p class="card-subtitle">Complete the bill of materials for your project</p>
            </div>
            <div class="card-body">
                <form action="../../public/bom/add_bom.php" method="post">
                    <input type="hidden" name="project_name" value="<?php echo htmlspecialchars($_GET['project_name'] ?? ''); ?>">
                    <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($_GET['customer_name'] ?? ''); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project_name_display" class="form-label">Project Name</label>
                            <input type="text" id="project_name_display" class="form-control" 
                                   value="<?php echo htmlspecialchars($_GET['project_name'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="customer_name_display" class="form-label">Customer Name</label>
                            <input type="text" id="customer_name_display" class="form-control" 
                                   value="<?php echo htmlspecialchars($_GET['customer_name'] ?? ''); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">📋</span>
                            Save BOM
                        </button>
                        <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-secondary">
                            <span class="icon">✖️</span>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Material Template -->
    <template id="material-template">
        <div class="card material-entry">
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Material Name</label>
                        <input type="text" class="material-name form-control" readonly>
                        <input type="hidden" class="material-id" name="material_id[]">
                    </div>
                </div>
                <div class="material-details">
                    <!-- Material details will be added here dynamically -->
                </div>
            </div>
        </div>
    </template>

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
                    
                    // Add material details
                    const materialDetails = materialTemplate.querySelector('.material-details');
                    materialDetails.innerHTML = `
                        <div class="form-row">
                            <div class="form-group">
                                <label for="length_${materialEntries}" class="form-label">Length</label>
                                <input type="number" step="0.01" id="length_${materialEntries}" name="length[]" 
                                       class="form-control length" value="${material.length}" required>
                            </div>
                            <div class="form-group">
                                <label for="width_${materialEntries}" class="form-label">Width</label>
                                <input type="number" step="0.01" id="width_${materialEntries}" name="width[]" 
                                       class="form-control width" value="${material.width}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="thickness_${materialEntries}" class="form-label">Thickness</label>
                                <input type="number" step="0.01" id="thickness_${materialEntries}" name="thickness[]" 
                                       class="form-control thickness" value="${material.thickness}" required>
                            </div>
                            <div class="form-group">
                                <label for="quantity_${materialEntries}" class="form-label">Quantity</label>
                                <input type="number" id="quantity_${materialEntries}" name="quantity[]" 
                                       class="form-control quantity" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeMaterial(${materialEntries})">
                                <span class="icon">🗑️</span>
                                Remove Material
                            </button>
                        </div>
                    `;
                    
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

        function searchMaterials() {
            const searchQuery = document.getElementById('material-search').value;
            if (searchQuery.length < 2) {
                document.getElementById('material-list').innerHTML = '';
                return;
            }
            
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
                    
                    if (data.length === 0) {
                        materialList.innerHTML = '<div class="notification notification-info">No materials found</div>';
                        return;
                    }
                    
                    data.forEach(material => {
                        const materialCard = document.createElement('div');
                        materialCard.className = 'card card-hover cursor-pointer mb-3';
                        materialCard.innerHTML = `
                            <div class="card-body">
                                <h4 class="card-title">${material.material_name}</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm text-muted">
                                    <div>Type: ${material.type}</div>
                                    <div>ID: ${material.id}</div>
                                    <div>L: ${material.length}</div>
                                    <div>W: ${material.width}</div>
                                    <div>T: ${material.thickness}</div>
                                </div>
                            </div>
                        `;
                        materialCard.onclick = () => addMaterial(material.id);
                        materialList.appendChild(materialCard);
                    });
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    alert('An error occurred while fetching materials: ' + error.message);
                });
        }

        function addNewProject() {
            window.location.href = '../../Views/projects/add_project.php';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const projectName = urlParams.get('project_name');
            const customerName = urlParams.get('customer_name');

            if (!projectName || !customerName) {
                alert('Project Name and Customer Name are required.');
                window.location.href = '../../Views/projects/add_project.php';
            }
        });
    </script>
</body>
</html>
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
