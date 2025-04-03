-- Add the `customer_id` column to the `projects` table
ALTER TABLE `projects`
ADD COLUMN `customer_id` INT AFTER `project_id`;

-- Add a foreign key constraint to link `customer_id` in `projects` to `customer_id` in `customers`
ALTER TABLE `projects`
ADD CONSTRAINT `fk_projects_customer_id`
FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`)
ON DELETE CASCADE
ON UPDATE CASCADE;