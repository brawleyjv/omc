-- ============================================
-- Etsy Order Items - Product Tracking
-- Created: December 19, 2025
-- Purpose: Track individual products/items from Etsy orders
-- Phase: 2.5 - Can be built without API approval
-- ============================================

-- ============================================
-- Etsy Order Items Table
-- ============================================

-- Track individual line items from Etsy orders
-- Links products to OMC projects for better reporting and estimates
CREATE TABLE IF NOT EXISTS etsy_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etsy_order_id INT NOT NULL COMMENT 'Links to etsy_orders.id',
    project_id INT NULL COMMENT 'Optional link to OMC projects',
    
    -- Etsy Item Identification
    etsy_listing_id BIGINT NULL COMMENT 'Etsy Listing ID',
    etsy_transaction_id BIGINT NULL COMMENT 'Etsy Transaction ID',
    
    -- Product Information
    product_name VARCHAR(255) NOT NULL COMMENT 'Product/Item Name',
    product_sku VARCHAR(100) NULL COMMENT 'SKU if provided',
    product_title VARCHAR(500) NULL COMMENT 'Full listing title',
    
    -- Pricing & Quantity
    quantity INT NOT NULL DEFAULT 1 COMMENT 'Quantity Ordered',
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Price Per Unit',
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Line Item Total (qty * price)',
    
    -- Customization/Variations
    variations_data JSON NULL COMMENT 'Product variations (size, color, etc)',
    personalization TEXT NULL COMMENT 'Customer personalization text',
    customization_notes TEXT NULL COMMENT 'Special customization requests',
    
    -- Product Linking Status
    auto_matched BOOLEAN DEFAULT FALSE COMMENT 'Automatically matched to project',
    manually_linked BOOLEAN DEFAULT FALSE COMMENT 'Manually linked by user',
    link_confidence DECIMAL(3,2) NULL COMMENT 'Confidence score 0.00-1.00',
    
    -- Raw Data
    item_data JSON NULL COMMENT 'Full item JSON from Etsy',
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Record Created',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record Updated',
    linked_at DATETIME NULL COMMENT 'Date linked to project',
    
    -- Foreign Keys
    FOREIGN KEY (etsy_order_id) REFERENCES etsy_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    
    -- Indexes for Performance
    INDEX idx_etsy_order_id (etsy_order_id),
    INDEX idx_project_id (project_id),
    INDEX idx_etsy_listing_id (etsy_listing_id),
    INDEX idx_etsy_transaction_id (etsy_transaction_id),
    INDEX idx_product_name (product_name),
    INDEX idx_product_sku (product_sku),
    INDEX idx_auto_matched (auto_matched),
    INDEX idx_manually_linked (manually_linked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Individual items from Etsy orders';

-- ============================================
-- Product Link Mappings Table
-- ============================================

-- Store permanent mappings between Etsy products and OMC projects
-- Once a product is linked, future orders auto-match
CREATE TABLE IF NOT EXISTS etsy_product_mappings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- Etsy Product Identification (multiple ways to match)
    etsy_listing_id BIGINT NULL COMMENT 'Etsy Listing ID',
    product_name VARCHAR(255) NULL COMMENT 'Exact product name match',
    product_sku VARCHAR(100) NULL COMMENT 'SKU match',
    product_title_pattern VARCHAR(500) NULL COMMENT 'Title pattern (% wildcards)',
    
    -- OMC Project Link
    project_id INT NOT NULL COMMENT 'Linked OMC Project',
    
    -- Mapping Metadata
    match_type VARCHAR(50) NOT NULL COMMENT 'listing_id, sku, name, pattern',
    created_by VARCHAR(100) NULL COMMENT 'User who created mapping',
    confidence DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Match confidence 0.00-1.00',
    active BOOLEAN DEFAULT TRUE COMMENT 'Is mapping active?',
    
    -- Statistics
    times_matched INT DEFAULT 0 COMMENT 'How many times this mapping was used',
    last_matched_at DATETIME NULL COMMENT 'Last time mapping was used',
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_etsy_listing_id (etsy_listing_id),
    INDEX idx_product_sku (product_sku),
    INDEX idx_product_name (product_name),
    INDEX idx_project_id (project_id),
    INDEX idx_match_type (match_type),
    INDEX idx_active (active),
    
    -- Unique constraint: one mapping per identifier per project
    UNIQUE KEY unique_listing_project (etsy_listing_id, project_id),
    UNIQUE KEY unique_sku_project (product_sku, project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Permanent product-to-project mappings';

-- ============================================
-- Add Project Linking Helper Column to etsy_orders
-- ============================================

-- Add a quick flag to show if order has unlinked products
ALTER TABLE etsy_orders ADD COLUMN has_unlinked_items BOOLEAN DEFAULT FALSE COMMENT 'Has items not linked to projects';
ALTER TABLE etsy_orders ADD INDEX idx_has_unlinked_items (has_unlinked_items);

-- ============================================
-- Verification Queries (Commented Out)
-- ============================================

-- Check table structure
-- DESCRIBE etsy_order_items;
-- DESCRIBE etsy_product_mappings;

-- Test query: Show all order items with their linked projects
-- SELECT 
--     eoi.id,
--     eoi.product_name,
--     eoi.quantity,
--     eoi.total_price,
--     p.project_name as linked_project,
--     eoi.auto_matched,
--     eoi.manually_linked
-- FROM etsy_order_items eoi
-- LEFT JOIN projects p ON eoi.project_id = p.id
-- ORDER BY eoi.created_at DESC
-- LIMIT 20;

-- Test query: Show all product mappings
-- SELECT 
--     epm.match_type,
--     epm.product_name,
--     epm.product_sku,
--     p.project_name,
--     epm.times_matched,
--     epm.active
-- FROM etsy_product_mappings epm
-- INNER JOIN projects p ON epm.project_id = p.id
-- ORDER BY epm.times_matched DESC;

-- Test query: Find orders with unlinked items
-- SELECT 
--     eo.etsy_order_id,
--     eo.customer_name,
--     COUNT(eoi.id) as total_items,
--     SUM(CASE WHEN eoi.project_id IS NULL THEN 1 ELSE 0 END) as unlinked_items
-- FROM etsy_orders eo
-- LEFT JOIN etsy_order_items eoi ON eo.id = eoi.etsy_order_id
-- GROUP BY eo.id
-- HAVING unlinked_items > 0;
