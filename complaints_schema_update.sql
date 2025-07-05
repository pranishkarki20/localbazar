-- Updated complaints table for user_id and role
ALTER TABLE complaints 
ADD COLUMN user_id INT DEFAULT NULL,
ADD COLUMN role VARCHAR(20) DEFAULT NULL;

-- If you want to recreate:
-- CREATE TABLE IF NOT EXISTS complaints (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     user_id INT DEFAULT NULL,
--     role VARCHAR(20) DEFAULT NULL,
--     name VARCHAR(100) NOT NULL,
--     email VARCHAR(120) NOT NULL,
--     subject VARCHAR(200) NOT NULL,
--     complaint TEXT NOT NULL,
--     created_at DATETIME NOT NULL,
--     is_resolved TINYINT(1) DEFAULT 0
-- );
