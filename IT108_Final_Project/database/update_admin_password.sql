-- Update admin password to admin123
-- Run this if you need to reset the admin password
-- This hash is for password: admin123

UPDATE users 
SET password_hash = '$2y$12$HviUx13Mpsoxz6ZzfBbdmuKTBJz9n8yhCS4VJiTp25FK2SuzyS2cG'
WHERE username = 'admin';

-- To generate a new hash for a different password, use PHP:
-- php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"


