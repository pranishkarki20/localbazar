-- Add price column to orders table if not present
ALTER TABLE orders ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0;
-- Make sure products table has price, on_sale, discount_percent columns
-- Example products table definition:
-- CREATE TABLE products (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(255),
--   price DECIMAL(10,2),
--   category VARCHAR(100),
--   img VARCHAR(255),
--   description TEXT,
--   merchant VARCHAR(255),
--   on_sale TINYINT(1) DEFAULT 0,
--   discount_percent INT DEFAULT 0
-- );
-- Example orders table definition:
-- CREATE TABLE orders (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   buyer_id INT,
--   seller_id INT,
--   product_id INT,
--   qty INT,
--   price DECIMAL(10,2),
--   address VARCHAR(255),
--   status VARCHAR(50),
--   order_time DATETIME
-- );
