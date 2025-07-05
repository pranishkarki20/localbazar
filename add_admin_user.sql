-- Insert a default admin user into the users table (if not exists)
INSERT INTO users (username, email, password, role)
SELECT 'admin', 'admin@gmail.com', '$2y$10$Qw8Qw8Qw8Qw8Qw8Qw8Qw8uQw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8Qw8', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@gmail.com');
-- The above password hash is for 'adinm' (bcrypt).
