-- ============================================
-- KasiBite Database Setup
-- Run this in phpMyAdmin or MySQL terminal
-- ============================================

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('buyer','seller','admin') DEFAULT 'buyer',
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    phone VARCHAR(20),
    location VARCHAR(100),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 1,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- CART TABLE
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
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

-- ORDER ITEMS TABLE
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- REVIEWS TABLE
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    buyer_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (buyer_id) REFERENCES users(user_id)
);

-- PASSWORD RESETS TABLE
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- SEED DATA
-- ============================================

-- Categories
INSERT INTO categories (category_name) VALUES
('Amagwinya'),
('Pap & Stew'),
('Grilled Meat'),
('Bunny Chow'),
('Breakfast'),
('Beverages'),
('Smiley & Walkie Talkies'),
('Umngqusho'),
('Snacks & Sides'),
('Vetkoek');

-- Admin user (password: admin123)
INSERT INTO users (full_name, email, password, role, status) VALUES
('Admin KasiBite', 'admin@kasibite.co.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'approved');

-- Demo seller (password: seller123)
INSERT INTO users (full_name, email, password, role, status, location, bio) VALUES
('Mama Thandi', 'thandi@kasibite.co.za', '$2y$10$TKh8H1.PfuTBNd5.3HqITuOefGS3kVmr3fnMGsxbFbOj/sPGBFImu', 'seller', 'approved', 'Soweto, Johannesburg', 'Best pap and stew in Soweto!');

-- Demo buyer (password: buyer123)
INSERT INTO users (full_name, email, password, role, status) VALUES
('Sipho Dlamini', 'sipho@kasibite.co.za', '$2y$10$8K3yGzW1Zyq4bPbL.1K2JO8qHQaT3xDl7RqtJYHlLMRKF2e0oVj6', 'buyer', 'approved');

-- Demo products (seller_id = 2 = Mama Thandi)
INSERT INTO products (seller_id, category_id, name, description, price, stock) VALUES
(2, 2, 'Pap ne Nyama', 'Creamy pap with tender beef stew and chakalaka on the side.', 45.00, 20),
(2, 1, 'Amagwinya x4', 'Fresh hot amagwinya, made to order. Comes with polony or cheese.', 25.00, 30),
(2, 3, 'Braai Pack', 'Grilled chicken, boerewors and chops. Served with pap and gravy.', 85.00, 10),
(2, 5, 'Kasi Breakfast', 'Eggs, boerewors, pap and fresh juice. The full kasi morning.', 55.00, 15),
(2, 10, 'Vetkoek x3', 'Golden vetkoek with mince filling. Pure township goodness.', 30.00, 25),
(2, 6, 'Mahewu', 'Traditional fermented maize drink. Cold and refreshing.', 15.00, 50);
