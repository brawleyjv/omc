-- ============================================
-- Etsy API Integration - Database Schema
-- Created: December 19, 2025
-- Purpose: Store Etsy API credentials, tokens, and order data
-- ============================================

-- ============================================
-- PART 1: Settings Table - Etsy Credentials
-- ============================================

-- Add Etsy API credentials and connection status to settings table
ALTER TABLE settings ADD COLUMN etsy_api_key VARCHAR(255) NULL COMMENT 'Etsy API Keystring';
ALTER TABLE settings ADD COLUMN etsy_shared_secret VARCHAR(255) NULL COMMENT 'Etsy API Shared Secret';
ALTER TABLE settings ADD COLUMN etsy_access_token TEXT NULL COMMENT 'OAuth 2.0 Access Token';
ALTER TABLE settings ADD COLUMN etsy_refresh_token TEXT NULL COMMENT 'OAuth 2.0 Refresh Token';
ALTER TABLE settings ADD COLUMN etsy_shop_id VARCHAR(100) NULL COMMENT 'Etsy Shop ID';
ALTER TABLE settings ADD COLUMN etsy_shop_name VARCHAR(255) NULL COMMENT 'Etsy Shop Name';
ALTER TABLE settings ADD COLUMN etsy_token_expires DATETIME NULL COMMENT 'Token Expiration Timestamp';
ALTER TABLE settings ADD COLUMN etsy_connected BOOLEAN DEFAULT FALSE COMMENT 'Connection Status';
ALTER TABLE settings ADD COLUMN etsy_last_sync DATETIME NULL COMMENT 'Last Successful Sync Timestamp';

-- Add indexes for better performance
ALTER TABLE settings ADD INDEX idx_etsy_connected (etsy_connected);

-- ============================================
-- PART 2: Etsy Orders Table
-- ============================================

-- Cache Etsy orders locally for performance and offline access
CREATE TABLE IF NOT EXISTS etsy_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etsy_order_id BIGINT UNIQUE NOT NULL COMMENT 'Etsy Receipt ID',
    etsy_receipt_id BIGINT NOT NULL COMMENT 'Etsy Receipt ID (same as order_id)',
    buyer_user_id BIGINT NULL COMMENT 'Etsy Buyer User ID',
    
    -- Customer Information
    customer_name VARCHAR(255) NULL COMMENT 'Buyer Name',
    customer_email VARCHAR(255) NULL COMMENT 'Buyer Email',
    
    -- Shipping Address (stored as JSON for flexibility)
    shipping_address TEXT NULL COMMENT 'Shipping Address JSON',
    shipping_name VARCHAR(255) NULL COMMENT 'Ship To Name',
    shipping_first_line VARCHAR(255) NULL COMMENT 'Address Line 1',
    shipping_second_line VARCHAR(255) NULL COMMENT 'Address Line 2',
    shipping_city VARCHAR(100) NULL COMMENT 'City',
    shipping_state VARCHAR(100) NULL COMMENT 'State/Province',
    shipping_zip VARCHAR(20) NULL COMMENT 'Postal Code',
    shipping_country VARCHAR(100) NULL COMMENT 'Country',
    
    -- Order Details
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Order Total',
    currency VARCHAR(3) DEFAULT 'USD' COMMENT 'Currency Code',
    status VARCHAR(50) NULL COMMENT 'Order Status (paid, shipped, completed)',
    
    -- Fulfillment Tracking
    shipped BOOLEAN DEFAULT FALSE COMMENT 'Marked as Shipped',
    tracking_code VARCHAR(255) NULL COMMENT 'Tracking Number',
    carrier_name VARCHAR(100) NULL COMMENT 'Shipping Carrier',
    ship_date DATETIME NULL COMMENT 'Date Shipped',
    
    -- Items (stored as JSON array)
    items_data JSON NULL COMMENT 'Order Items JSON Array',
    item_count INT DEFAULT 0 COMMENT 'Number of Items',
    
    -- Raw Data Storage (for debugging and future use)
    order_data JSON NULL COMMENT 'Full Etsy Order JSON',
    
    -- Timestamps
    etsy_created_at DATETIME NULL COMMENT 'Order Creation Date on Etsy',
    etsy_updated_at DATETIME NULL COMMENT 'Last Update on Etsy',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Record Created in OMC',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Record Updated in OMC',
    synced_at DATETIME NULL COMMENT 'Last Synced from Etsy API',
    
    -- Links to OMC System
    estimate_id INT NULL COMMENT 'Linked OMC Estimate',
    customer_id INT NULL COMMENT 'Linked OMC Customer',
    
    -- Indexes for performance
    INDEX idx_etsy_order_id (etsy_order_id),
    INDEX idx_buyer_user_id (buyer_user_id),
    INDEX idx_status (status),
    INDEX idx_shipped (shipped),
    INDEX idx_customer_email (customer_email),
    INDEX idx_synced_at (synced_at),
    INDEX idx_estimate_id (estimate_id),
    INDEX idx_customer_id (customer_id),
    
    -- Foreign Keys
    FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Etsy Orders Cache';

-- ============================================
-- PART 3: Etsy Sync Log Table
-- ============================================

-- Track all API sync operations for debugging and monitoring
CREATE TABLE IF NOT EXISTS etsy_sync_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sync_type VARCHAR(50) NOT NULL COMMENT 'Type: orders, listings, inventory, fulfillment',
    status VARCHAR(20) NOT NULL COMMENT 'Status: success, failure, partial',
    
    -- Sync Statistics
    records_processed INT DEFAULT 0 COMMENT 'Number of Records Processed',
    records_added INT DEFAULT 0 COMMENT 'New Records Added',
    records_updated INT DEFAULT 0 COMMENT 'Records Updated',
    records_failed INT DEFAULT 0 COMMENT 'Failed Records',
    
    -- Error Information
    error_message TEXT NULL COMMENT 'Error Details',
    error_code VARCHAR(50) NULL COMMENT 'API Error Code',
    
    -- API Rate Limiting
    api_calls_made INT DEFAULT 0 COMMENT 'Number of API Calls',
    rate_limit_remaining INT NULL COMMENT 'Remaining API Calls',
    
    -- Timestamps
    started_at DATETIME NULL COMMENT 'Sync Start Time',
    completed_at DATETIME NULL COMMENT 'Sync Completion Time',
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Log Entry Created',
    
    -- Indexes
    INDEX idx_sync_type (sync_type),
    INDEX idx_status (status),
    INDEX idx_synced_at (synced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Etsy API Sync Audit Log';

-- ============================================
-- PART 4: Initial Data
-- ============================================

-- Set initial Etsy API credentials (if settings table has a row)
-- Note: This will be updated via Settings page UI
UPDATE settings 
SET 
    etsy_api_key = 'w2umgp6l4u16xywc9fmuq0jn',
    etsy_shared_secret = 'zeshg5z9v3',
    etsy_connected = FALSE
WHERE id = 1;

-- ============================================
-- Verification Queries (Commented Out)
-- ============================================

-- Check settings table for Etsy fields
-- SELECT id, etsy_api_key, etsy_shop_name, etsy_connected, etsy_last_sync FROM settings;

-- Check etsy_orders table structure
-- DESCRIBE etsy_orders;

-- Check recent sync logs
-- SELECT * FROM etsy_sync_log ORDER BY synced_at DESC LIMIT 10;

-- Count orders by status
-- SELECT status, COUNT(*) as count FROM etsy_orders GROUP BY status;
