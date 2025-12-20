-- Add email configuration fields to settings table
-- Created: 2025-12-19
-- Purpose: Add SMTP email server settings for sending estimate emails

ALTER TABLE settings 
ADD COLUMN IF NOT EXISTS smtp_host VARCHAR(255),
ADD COLUMN IF NOT EXISTS smtp_port INT DEFAULT 587,
ADD COLUMN IF NOT EXISTS smtp_username VARCHAR(255),
ADD COLUMN IF NOT EXISTS smtp_password VARCHAR(255),
ADD COLUMN IF NOT EXISTS smtp_from_email VARCHAR(100),
ADD COLUMN IF NOT EXISTS smtp_from_name VARCHAR(100),
ADD COLUMN IF NOT EXISTS smtp_encryption VARCHAR(10) DEFAULT 'tls';
