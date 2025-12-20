-- Sync Production Batch Costs from Estimates
-- This migration calculates material_cost and labor_cost for production batches
-- based on their linked project's estimate costs
-- 
-- Cost Calculation:
-- - material_cost = estimate.materials_cost * batch.quantity_produced
-- - labor_cost = (estimate.labor_cost + estimate.machine_cost) * batch.quantity_produced
--
-- Date: December 20, 2025

UPDATE production_batches pb
INNER JOIN projects p ON pb.project_id = p.id
INNER JOIN estimates e ON p.estimate_id = e.id
SET 
    pb.material_cost = e.materials_cost * pb.quantity_produced,
    pb.labor_cost = (e.labor_cost + e.machine_cost) * pb.quantity_produced
WHERE 
    pb.material_cost IS NULL 
    OR pb.labor_cost IS NULL
    OR pb.material_cost = 0 
    OR pb.labor_cost = 0;

-- Verification Query (run after migration to verify results)
-- SELECT 
--     pb.batch_number,
--     p.project_name,
--     pb.quantity_produced,
--     e.materials_cost as est_materials_per_unit,
--     e.labor_cost as est_labor_per_unit,
--     e.machine_cost as est_machine_per_unit,
--     pb.material_cost as batch_material_cost,
--     pb.labor_cost as batch_labor_cost,
--     (pb.material_cost + pb.labor_cost) as batch_total_cost
-- FROM production_batches pb
-- INNER JOIN projects p ON pb.project_id = p.id
-- INNER JOIN estimates e ON p.estimate_id = e.id
-- ORDER BY pb.production_date DESC;
