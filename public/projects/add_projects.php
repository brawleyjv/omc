<form method="POST" action="add_projects_handler.php" enctype="multipart/form-data">
    <!-- ...existing code... -->
    <label for="project_name">Project Name:</label>
    <input type="text" id="project_name" name="project_name" required>
    
    <label for="design_date">Design Date:</label>
    <input type="date" id="design_date" name="design_date" required>
    
    <!-- Removed customer name field -->
    
    <label for="laser_time">Laser Time:</label>
    <input type="number" id="laser_time" name="laser_time" required>
    
    <label for="router_time">Router Time:</label>
    <input type="number" id="router_time" name="router_time" required>
    
    <label for="labor_hours">Labor Hours:</label>
    <input type="number" id="labor_hours" name="labor_hours" required>
    
    <label for="project_description">Project Description:</label>
    <textarea id="project_description" name="project_description" required></textarea>
    
    <label for="due_date">Due Date:</label>
    <input type="date" id="due_date" name="due_date" required>
    
    <label for="file_upload">File Upload:</label>
    <input type="file" id="file_upload" name="file_upload">
    
    <label for="image_upload">Image Upload:</label>
    <input type="file" id="image_upload" name="image_upload">
    
    <label for="design_file">Design File:</label>
    <input type="file" id="design_file" name="design_file">
    
    <button type="submit">Add Project</button>
</form>