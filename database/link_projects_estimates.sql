-- ============================================
-- Project-Estimate Integration Schema Changes
-- Created: December 19, 2025
-- Purpose: Link projects with estimates and allow project estimates without customers
-- ============================================

-- Note: projects.estimate_id already exists in the table

-- 1. Make customer_name optional in estimates (allow project estimates without customers)
ALTER TABLE estimates MODIFY COLUMN customer_name VARCHAR(255) NULL COMMENT 'Customer name - optional for project estimates';

-- 2. Add flag to distinguish project estimates from customer estimates
ALTER TABLE estimates ADD COLUMN is_project_estimate BOOLEAN DEFAULT FALSE COMMENT 'True if estimate was created from a project without customer';

-- 3. Add index on is_project_estimate for filtering
ALTER TABLE estimates ADD INDEX idx_is_project_estimate (is_project_estimate);

-- Note: project_name and project_id already exist in estimates table
-- Note: estimate_id column already exists in projects table

-- ============================================
-- Data Migration (Optional)
-- Link existing projects to estimates by matching names
-- Run this carefully - review matches first!
-- ============================================

-- COMMENTED OUT FOR SAFETY - Uncomment after reviewing matches
-- UPDATE projects p
-- JOIN estimates e ON p.project_name = e.project_name
-- SET p.estimate_id = e.id
-- WHERE p.estimate_id IS NULL;

-- ============================================
-- Verification Queries
-- ============================================

-- Check projects with estimates
-- SELECT p.project_name, p.estimate_id, e.estimate_number
-- FROM projects p
-- LEFT JOIN estimates e ON p.estimate_id = e.id;

-- Check project estimates (no customer)
-- SELECT estimate_number, project_name, is_project_estimate, customer_id
-- FROM estimates
-- WHERE is_project_estimate = TRUE OR customer_id IS NULL;
