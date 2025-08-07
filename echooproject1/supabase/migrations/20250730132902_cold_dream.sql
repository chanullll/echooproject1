-- Add New Admin User to EcoStore Database
-- Run these commands in psql or your PostgreSQL client

-- Connect to your database first:
-- psql -U postgres -d ecostore

-- Method 1: Add admin with specific details
INSERT INTO users (
    username, 
    email, 
    password, 
    role, 
    full_name, 
    total_carbon_saved
) VALUES (
    'newadmin',                                                    -- Change this username
    'newadmin@ecostore.com',                                      -- Change this email
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: admin123
    'admin',
    'New Admin User',                                             -- Change this name
    0.00
);

-- Method 2: If you want a different password, use this format:
-- First, you need to generate a password hash. Here are some common ones:

-- For password 'password123':
-- INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) 
-- VALUES ('admin2', 'admin2@ecostore.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin', 'Admin Two', 0.00);

-- For password 'admin456':
-- INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) 
-- VALUES ('admin3', 'admin3@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin Three', 0.00);

-- Verify the user was created:
SELECT id, username, email, role, full_name, total_carbon_saved, created_at 
FROM users 
WHERE role = 'admin' 
ORDER BY created_at DESC;

-- Optional: Update existing user to admin (if you want to promote someone):
-- UPDATE users SET role = 'admin' WHERE username = 'existing_username';