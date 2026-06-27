ALTER TABLE users
  ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS email_verification_token VARCHAR(120) NULL,
  ADD COLUMN IF NOT EXISTS business_name VARCHAR(150) NULL,
  ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL,
  ADD COLUMN IF NOT EXISTS id_number VARCHAR(80) NULL,
  ADD COLUMN IF NOT EXISTS verification_details TEXT NULL,
  ADD COLUMN IF NOT EXISTS wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00;

ALTER TABLE categories
  ADD COLUMN IF NOT EXISTS image_url TEXT NULL;

ALTER TABLE products
  ADD COLUMN IF NOT EXISTS image_url TEXT NULL;

ALTER TABLE cart
  ADD COLUMN IF NOT EXISTS special_instructions TEXT NULL;

ALTER TABLE order_items
  ADD COLUMN IF NOT EXISTS special_instructions TEXT NULL;

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(30) NOT NULL DEFAULT 'card',
  ADD COLUMN IF NOT EXISTS payment_status VARCHAR(30) NOT NULL DEFAULT 'paid',
  ADD COLUMN IF NOT EXISTS delivery_notes TEXT NULL;

CREATE TABLE IF NOT EXISTS seller_payouts (
  payout_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  seller_id INT NOT NULL,
  product_id INT NOT NULL,
  gross_amount DECIMAL(10,2) NOT NULL,
  platform_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  seller_amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(order_id),
  INDEX(seller_id)
);

CREATE TABLE IF NOT EXISTS messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  product_id INT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(buyer_id),
  INDEX(seller_id),
  INDEX(product_id)
);

CREATE TABLE IF NOT EXISTS reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  product_id INT NOT NULL,
  order_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_buyer_product_order (buyer_id, product_id, order_id),
  INDEX(seller_id),
  INDEX(product_id)
);

CREATE TABLE IF NOT EXISTS disputes (
  dispute_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  product_id INT NULL,
  reason VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(order_id),
  INDEX(buyer_id),
  INDEX(seller_id)
);

CREATE TABLE IF NOT EXISTS password_resets (
  reset_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  token VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(email),
  INDEX(token)
);
