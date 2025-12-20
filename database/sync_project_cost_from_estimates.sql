-- ============================================
-- Sync Project Cost Per Unit from Estimates
-- Created: December 20, 2025
-- Purpose: Ensure ready/active projects have cost_per_unit from their estimates
-- ============================================

-- Update existing ready/active projects to get cost_per_unit from their linked estimates
UPDATE projects p
INNER JOIN estimates e ON p.estimate_id = e.id
SET p.cost_per_unit = e.total_estimate
WHERE p.production_status IN ('ready', 'active')
AND (p.cost_per_unit IS NULL OR p.cost_per_unit = 0);

-- ============================================
-- Verification Query
-- ============================================

-- Check projects that are ready/active but missing cost_per_unit
SELECT 
    p.id,
    p.project_name,
    p.production_status,
    p.estimate_id,
    e.estimate_number,
    e.total_estimate,
    p.cost_per_unit,
    CASE 
        WHEN p.estimate_id IS NULL THEN 'No Estimate Linked'
        WHEN e.total_estimate IS NULL OR e.total_estimate = 0 THEN 'Estimate Has No Value'
        WHEN p.cost_per_unit IS NULL THEN 'Cost Per Unit Not Set'
        ELSE 'OK'
    END as status
FROM projects p
LEFT JOIN estimates e ON p.estimate_id = e.id
WHERE p.production_status IN ('ready', 'active')
ORDER BY p.project_name;

-- ============================================
-- Notes
-- ============================================

-- This migration ensures that:
-- 1. All ready/active projects have a cost_per_unit value
-- 2. The value comes from their linked estimate's total_estimate
-- 3. Future status changes will auto-populate this (handled in list_projects.php)
-- 
-- Business Rule:
-- - Projects in 'design' or 'discontinued' status don't need cost_per_unit
-- - Projects must have an estimate to be changed to 'ready' or 'active' status
-- - When status changes to ready/active, cost_per_unit is copied from estimate
-- - Inventory value = inventory_quantity * cost_per_unit (from estimate)
