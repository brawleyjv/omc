-- Add company information fields to settings table
-- Created: 2025-12-19
-- Purpose: Add fields for company address, contact info, and logo

ALTER TABLE settings 
ADD COLUMN IF NOT EXISTS company_address VARCHAR(255),
ADD COLUMN IF NOT EXISTS company_city VARCHAR(100),
ADD COLUMN IF NOT EXISTS company_state VARCHAR(2),
ADD COLUMN IF NOT EXISTS company_zip VARCHAR(10),
ADD COLUMN IF NOT EXISTS company_phone VARCHAR(20),
ADD COLUMN IF NOT EXISTS company_email VARCHAR(100),
ADD COLUMN IF NOT EXISTS company_logo VARCHAR(255);
