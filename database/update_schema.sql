-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop the existing `projects` table if it exists
DROP TABLE IF EXISTS `projects`;

-- Drop the existing `customer_project` table if it exists
DROP TABLE IF EXISTS `customer_project`;

-- Create the `projects` table
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

-- Create the `customer_project` table
CREATE TABLE `customer_project` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `project_id` INT NOT NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`project_id`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Drop the `customer_id` column from the `projects` table
ALTER TABLE `projects`
DROP COLUMN `customer_id`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
