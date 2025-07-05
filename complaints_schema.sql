-- Add this to your localbazar database
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    complaint TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0
);
