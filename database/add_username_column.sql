-- Add username column to users table
-- This migration adds a username field that was missing from the users table

USE omc;

-- Add username column after id
ALTER TABLE users ADD COLUMN username VARCHAR(100) UNIQUE AFTER id;

-- Update existing users to have username based on their name (temporary solution)
-- Remove spaces and make lowercase for username
UPDATE users SET username = LOWER(REPLACE(name, ' ', '')) WHERE username IS NULL;

-- Make sure all users have a username
UPDATE users SET username = CONCAT('user', id) WHERE username IS NULL OR username = '';

-- Show the updated structure
DESCRIBE users;

-- Show updated data
SELECT id, username, name, user_type FROM users;
