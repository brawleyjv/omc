-- Add estimate_id to projects table
-- This links projects to their base estimates for cost calculations
-- Created: December 20, 2025

ALTER TABLE projects 
ADD COLUMN estimate_id INT NULL 
COMMENT 'Links to estimates.id for cost calculations';

-- Add index for faster lookups
ALTER TABLE projects 
ADD INDEX idx_estimate_id (estimate_id);

-- Add foreign key constraint (optional - comment out if estimates table doesn't exist yet)
-- ALTER TABLE projects 
-- ADD CONSTRAINT fk_projects_estimate 
-- FOREIGN KEY (estimate_id) REFERENCES estimates(id) 
-- ON DELETE SET NULL;
