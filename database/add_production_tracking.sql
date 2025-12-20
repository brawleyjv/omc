-- ============================================
-- Production & Inventory Management
-- Created: December 19, 2025
-- Updated: December 19, 2025 - Added laser_time and mill_time columns (tracked in MINUTES)
-- Phase: 4A - Production Tracking (No API Required)
-- Purpose: Track product lifecycle from design → production → inventory
-- Note: Laser and mill times are in MINUTES, labor_hours is in HOURS
-- ============================================

-- ============================================
-- PART 1: Add Production Fields to Projects Table
-- ============================================

-- Add production status tracking
ALTER TABLE projects ADD COLUMN production_status ENUM('design', 'ready', 'active', 'discontinued') 
    DEFAULT 'design' 
    COMMENT 'Production lifecycle status';

-- Add Etsy listing link (for future Phase 4B)
ALTER TABLE projects ADD COLUMN etsy_listing_id BIGINT NULL 
    COMMENT 'Links to Etsy listing when published';

-- Add inventory tracking
ALTER TABLE projects ADD COLUMN inventory_quantity INT DEFAULT 0 
    COMMENT 'Current inventory on hand';

ALTER TABLE projects ADD COLUMN reorder_point INT DEFAULT 5 
    COMMENT 'Alert when inventory reaches this level';

ALTER TABLE projects ADD COLUMN batch_size INT DEFAULT 10 
    COMMENT 'Typical production batch size';

-- Add cost tracking
ALTER TABLE projects ADD COLUMN cost_per_unit DECIMAL(10,2) NULL 
    COMMENT 'Average cost per unit (from production batches)';

-- Add sync timestamp
ALTER TABLE projects ADD COLUMN last_inventory_sync DATETIME NULL 
    COMMENT 'Last time inventory was synced with Etsy';

-- Add indexes for performance
ALTER TABLE projects ADD INDEX idx_production_status (production_status);
ALTER TABLE projects ADD INDEX idx_inventory_low_stock (inventory_quantity, reorder_point);
ALTER TABLE projects ADD INDEX idx_etsy_listing (etsy_listing_id);

-- ============================================
-- PART 2: Production Batches Table
-- ============================================

CREATE TABLE IF NOT EXISTS production_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT 'Links to projects table',
    
    -- Batch Information
    batch_number VARCHAR(50) NULL COMMENT 'Optional batch identifier (e.g., "2025-001")',
    quantity_produced INT NOT NULL COMMENT 'Number of units produced in this batch',
    production_date DATE NOT NULL COMMENT 'Date production was completed',
    
    -- Cost Tracking
    labor_hours DECIMAL(5,2) NULL COMMENT 'Total labor hours for this batch',
    laser_time DECIMAL(5,2) NULL COMMENT 'CNC laser time in minutes',
    mill_time DECIMAL(5,2) NULL COMMENT 'CNC mill time in minutes',
    material_cost DECIMAL(10,2) NULL COMMENT 'Total material cost for this batch',
    labor_cost DECIMAL(10,2) NULL COMMENT 'Total labor cost for this batch',
    cost_per_unit DECIMAL(10,2) NULL COMMENT 'Calculated: (material + labor) / quantity',
    
    -- Additional Info
    notes TEXT NULL COMMENT 'Production notes, issues, quality control',
    produced_by VARCHAR(100) NULL COMMENT 'Who produced this batch',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record created',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record updated',
    
    -- Foreign Keys
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_project (project_id),
    INDEX idx_date (production_date),
    INDEX idx_batch_number (batch_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Track production batches and costs';

-- ============================================
-- PART 3: Inventory Transactions Table
-- ============================================

CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT 'Links to projects table',
    
    -- Transaction Details
    transaction_type ENUM('production', 'sale', 'adjustment', 'damage', 'return') NOT NULL 
        COMMENT 'Type of inventory movement',
    quantity INT NOT NULL 
        COMMENT 'Quantity change (positive = increase, negative = decrease)',
    
    -- Inventory Snapshot
    quantity_before INT NOT NULL COMMENT 'Inventory before transaction',
    quantity_after INT NOT NULL COMMENT 'Inventory after transaction',
    
    -- Reference to Source
    reference_type VARCHAR(50) NULL 
        COMMENT 'Source type: production_batch, etsy_order, manual, etc',
    reference_id INT NULL 
        COMMENT 'ID of the source record',
    
    -- Additional Info
    notes TEXT NULL COMMENT 'Transaction notes/reason',
    created_by VARCHAR(100) NULL COMMENT 'Who made this transaction',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Transaction timestamp',
    
    -- Foreign Keys
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_project (project_id),
    INDEX idx_type (transaction_type),
    INDEX idx_created_at (created_at),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Audit trail for all inventory movements';

-- ============================================
-- PART 4: Link Production Batches to Order Fulfillment
-- ============================================

-- Add link from order items to production batch (for tracking which batch fulfilled which order)
ALTER TABLE etsy_order_items ADD COLUMN fulfilled_from_batch INT NULL 
    COMMENT 'Links to production_batches.id when order is fulfilled';

ALTER TABLE etsy_order_items ADD FOREIGN KEY (fulfilled_from_batch) 
    REFERENCES production_batches(id) ON DELETE SET NULL;

ALTER TABLE etsy_order_items ADD INDEX idx_fulfilled_from_batch (fulfilled_from_batch);

-- ============================================
-- Verification Queries (Commented Out)
-- ============================================

-- Check new project columns
-- SELECT id, project_name, production_status, inventory_quantity, reorder_point, batch_size
-- FROM projects LIMIT 5;

-- Check production batches table
-- DESCRIBE production_batches;

-- Check inventory transactions table
-- DESCRIBE inventory_transactions;

-- Check projects ready for production
-- SELECT project_name, production_status, inventory_quantity, reorder_point
-- FROM projects
-- WHERE production_status = 'ready'
-- ORDER BY project_name;

-- Check low stock items
-- SELECT project_name, inventory_quantity, reorder_point,
--        (reorder_point - inventory_quantity) as units_needed
-- FROM projects
-- WHERE inventory_quantity <= reorder_point
--   AND production_status IN ('ready', 'active')
-- ORDER BY units_needed DESC;

-- ============================================
-- Migration Complete
-- ============================================

-- Production tracking tables created successfully!
-- Next steps:
-- 1. Create UI to record production batches
-- 2. Create inventory dashboard
-- 3. Add production status to project list
-- 4. Build low stock alerts
