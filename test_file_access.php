<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Access Test - OMC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles-modern.css">
</head>
<body>
    <div class="main-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">File Access Functionality Test</h2>
                <p class="card-subtitle">Testing the expandable file list feature</p>
            </div>
            <div class="card-body">
                <h3>Enhanced File Display Features:</h3>
                <ul class="mb-4">
                    <li>✅ Show first 2 files by default</li>
                    <li>✅ Show "+X more files" button for additional files</li>
                    <li>✅ Click to expand and show all files</li>
                    <li>✅ Show first image thumbnail by default</li>
                    <li>✅ Show "+X more images" button for additional images</li>
                    <li>✅ Click to expand and show all image thumbnails</li>
                    <li>✅ All files are downloadable</li>
                    <li>✅ All images are clickable for full view</li>
                </ul>

                <div class="files-info" style="border: 1px solid #ddd; padding: 1rem; border-radius: 8px;">
                    <h4>Example File Display:</h4>
                    
                    <!-- Demo Files Section -->
                    <div class="text-sm"><a href="#" class="text-link">📄 project_design.dwg</a></div>
                    <div class="text-sm"><a href="#" class="text-link">📄 cutting_template.svg</a></div>
                    
                    <div class="text-sm">
                        <button type="button" class="btn-link text-muted" onclick="toggleFiles('demo-files')">
                            <span id="demo-files-toggle">+3 more files</span>
                        </button>
                    </div>
                    <div id="demo-files" class="additional-files" style="display: none;">
                        <div class="text-sm"><a href="#" class="text-link">📄 material_list.pdf</a></div>
                        <div class="text-sm"><a href="#" class="text-link">📄 assembly_instructions.pdf</a></div>
                        <div class="text-sm"><a href="#" class="text-link">📄 safety_notes.txt</a></div>
                    </div>

                    <!-- Demo Images Section -->
                    <div class="mt-3">
                        <h5>Images:</h5>
                        <div class="image-thumbnail-container">
                            <div style="width: 50px; height: 50px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                🖼️
                            </div>
                        </div>
                        
                        <div class="text-sm">
                            <button type="button" class="btn-link text-muted" onclick="toggleFiles('demo-images')">
                                <span id="demo-images-toggle">+2 more images</span>
                            </button>
                        </div>
                        <div id="demo-images" class="additional-images" style="display: none;">
                            <div class="image-thumbnail-container">
                                <div style="width: 30px; height: 30px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; margin-right: 0.5rem; cursor: pointer;">🖼️</div>
                            </div>
                            <div class="image-thumbnail-container">
                                <div style="width: 30px; height: 30px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; margin-right: 0.5rem; cursor: pointer;">🖼️</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>Views/projects/list_projects.php" class="btn btn-primary">
                        <span class="icon">📋</span>
                        Test Live Project List
                    </a>
                    <a href="<?php echo BASE_URL; ?>Views/main.php" class="btn btn-secondary">
                        <span class="icon">🏠</span>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFiles(elementId) {
            const element = document.getElementById(elementId);
            const isHidden = element.style.display === 'none';
            
            if (isHidden) {
                element.style.display = 'block';
                // Update toggle text to show "Hide"
                const toggleElement = document.getElementById(elementId + '-toggle');
                if (toggleElement) {
                    toggleElement.textContent = 'Hide additional ' + (elementId.includes('files') ? 'files' : 'images');
                }
            } else {
                element.style.display = 'none';
                // Restore original toggle text
                const toggleElement = document.getElementById(elementId + '-toggle');
                if (toggleElement) {
                    if (elementId.includes('files')) {
                        toggleElement.textContent = '+3 more files';
                    } else {
                        toggleElement.textContent = '+2 more images';
                    }
                }
            }
        }
    </script>

    <style>
        .btn-link {
            background: none;
            border: none;
            color: var(--color-primary);
            text-decoration: underline;
            cursor: pointer;
            font-size: inherit;
            padding: 0;
        }
        
        .btn-link:hover {
            color: var(--color-primary-dark);
        }
        
        .additional-files,
        .additional-images {
            margin-top: 0.25rem;
            padding-top: 0.25rem;
            border-top: 1px solid var(--color-border-light);
        }
        
        .image-thumbnail.small {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .image-thumbnail-container {
            display: inline-block;
            margin-right: 0.5rem;
        }
    </style>
</body>
</html>
