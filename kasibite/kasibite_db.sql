-- ============================================
-- KasiBite Database Setup v2
-- Run this in phpMyAdmin: DROP existing, reimport
-- ============================================

CREATE DATABASE IF NOT EXISTS kasibite;
USE kasibite;

-- USERS TABLE (extended with seller verification fields)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('buyer','seller','admin') DEFAULT 'buyer',
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    phone VARCHAR(20),
    location VARCHAR(150),
    bio TEXT,
    -- Seller verification fields
    sa_id_number VARCHAR(20),
    stall_address VARCHAR(255),
    food_safety_declaration TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- PRODUCTS TABLE (with custom image path)
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 10,
    status ENUM('active','inactive') DEFAULT 'active',
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- CART TABLE (with special instructions)
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    special_instructions TEXT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- ORDERS TABLE
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    payment_ref VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id)
);

-- ORDER ITEMS TABLE (with special instructions carried over)
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    special_instructions TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- REVIEWS TABLE
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    buyer_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (product_id, buyer_id, order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- PASSWORD RESETS TABLE
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- DISPUTES TABLE
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

-- ============================================
-- SEED DATA
-- ============================================
INSERT INTO categories (category_name) VALUES
('Amagwinya'),('Pap & Stew'),('Grilled Meat'),('Bunny Chow'),
('Breakfast'),('Beverages'),('Smiley & Walkie Talkies'),
('Umngqusho'),('Snacks & Sides'),('Vetkoek');

-- Admin (password: admin123)
INSERT INTO users (full_name,email,password,role,status) VALUES
('Admin KasiBite','admin@kasibite.co.za',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','approved');

-- Demo seller (password: seller123)
INSERT INTO users (full_name,email,password,role,status,phone,location,bio,sa_id_number,stall_address,food_safety_declaration) VALUES
('Mama Thandi','thandi@kasibite.co.za',
'$2y$10$TKh8H1.PfuTBNd5.3HqITuOefGS3kVmr3fnMGsxbFbOj/sPGBFImu',
'seller','approved','0712345678','Soweto, Johannesburg',
'Best pap and stew in Soweto for 10 years!',
'9001015009087','Stall 12, Soweto Taxi Rank, Johannesburg',1);

-- Demo buyer (password: buyer123)
INSERT INTO users (full_name,email,password,role,status) VALUES
('Sipho Dlamini','sipho@kasibite.co.za',
'$2y$10$8K3yGzW1Zyq4bPbL.1K2JO8qHQaT3xDl7RqtJYHlLMRKF2e0oVj6','buyer','approved');

-- Demo products
INSERT INTO products (seller_id,category_id,name,description,price,stock) VALUES
(2,2,'Pap ne Nyama','Creamy pap with tender beef stew and chakalaka on the side.',45.00,20),
(2,1,'Amagwinya x4','Fresh hot amagwinya, made to order. Comes with polony or cheese.',25.00,30),
(2,3,'Braai Pack','Grilled chicken, boerewors and chops. Served with pap and gravy.',85.00,10),
(2,5,'Kasi Breakfast','Eggs, boerewors, pap and fresh juice. The full kasi morning.',55.00,15),
(2,10,'Vetkoek x3','Golden vetkoek with mince filling. Pure township goodness.',30.00,25),
(2,6,'Mahewu','Traditional fermented maize drink. Cold and refreshing.',15.00,50);
