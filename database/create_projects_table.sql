CREATE TABLE `projects` (
    `project_id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_name` VARCHAR(255) NOT NULL,
    `design_date` DATE NOT NULL,
    `laser_time` INT NOT NULL,
    `router_time` INT NOT NULL,
    `labor_hours` INT NOT NULL,
    `project_description` TEXT NOT NULL,
    `due_date` DATE NOT NULL,
    `file_upload` VARCHAR(255),
    `image_upload` VARCHAR(255)
);
