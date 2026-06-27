-- ============================================
-- KasiBite Migration v2 — Run in phpMyAdmin
-- Adds all new columns to your existing tables
-- Safe to run even if columns already exist
-- ============================================
USE kasibite;

-- USERS: seller verification fields
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS sa_id_number VARCHAR(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS stall_address VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS food_safety_declaration TINYINT(1) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS rejection_reason VARCHAR(255) DEFAULT NULL;

-- PRODUCTS: custom image upload path
ALTER TABLE products
    ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) DEFAULT NULL;

-- CART: buyer special instructions per item
ALTER TABLE cart
    ADD COLUMN IF NOT EXISTS special_instructions TEXT DEFAULT NULL;

-- ORDER_ITEMS: carry special instructions through to order
ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS special_instructions TEXT DEFAULT NULL;

-- REVIEWS: tie to a specific order (verified purchase)
ALTER TABLE reviews
    ADD COLUMN IF NOT EXISTS order_id INT DEFAULT NULL;

-- DISPUTES: full disputes table (create if not exists)
CREATE TABLE IF NOT EXISTS disputes (
    dispute_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('open','reviewing','resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (seller_id) REFERENCES users(user_id)
);

SELECT 'Migration complete!' as result;
