-- Remove unused rate columns from setup table
-- This removes overhead_rate and packaging_rate as they are not used in the estimate flow
-- Run this on both local and production databases

ALTER TABLE setup DROP COLUMN overhead_rate;
ALTER TABLE setup DROP COLUMN packaging_rate;
