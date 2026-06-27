-- ============================================
-- KasiBite Migration v2
-- Run this ONLY if you already have a kasibite database
-- with data you want to keep. This ALTERS existing tables
-- instead of recreating them.
-- ============================================
USE kasibite;

-- Add verification fields to users
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone VARCHAR(20),
    ADD COLUMN IF NOT EXISTS id_number VARCHAR(20),
    ADD COLUMN IF NOT EXISTS stall_address VARCHAR(255),
    ADD COLUMN IF NOT EXISTS food_safety_declared TINYINT(1) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS rejection_reason VARCHAR(255);

-- Add custom image support to categories
ALTER TABLE categories
    ADD COLUMN IF NOT EXISTS default_image VARCHAR(500);

-- Add custom image to products
ALTER TABLE products
    ADD COLUMN IF NOT EXISTS image VARCHAR(500);

-- Add notes to cart
ALTER TABLE cart
    ADD COLUMN IF NOT EXISTS notes VARCHAR(500);

-- Add seller_id, notes, payout tracking to order_items
ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS seller_id INT,
    ADD COLUMN IF NOT EXISTS notes VARCHAR(500),
    ADD COLUMN IF NOT EXISTS payout_status ENUM('pending','released') DEFAULT 'pending';

-- Backfill seller_id on existing order_items from the products table
UPDATE order_items oi
JOIN products p ON oi.product_id = p.product_id
SET oi.seller_id = p.seller_id
WHERE oi.seller_id IS NULL;

-- Add order_id to reviews (so reviews are tied to a verified purchase)
ALTER TABLE reviews
    ADD COLUMN IF NOT EXISTS order_id INT;

-- Backfill default category images
UPDATE categories SET default_image = 'https://iol-prod.appspot.com/image/0a866824961ebe8a09ad8875ebac339f70fdbe4e=w700' WHERE category_name = 'Amagwinya' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://th.bing.com/th/id/R.79bd82699014d8e920c92c40ef5436ff?rik=raN2D5pdTbIujQ&pid=ImgRaw&r=0' WHERE category_name = 'Pap & Stew' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://www.suburbansimplicity.com/wp-content/uploads/2021/06/How-to-keep-meat-moist-on-the-grill.jpg' WHERE category_name = 'Grilled Meat' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d' WHERE category_name = 'Bunny Chow' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://tse3.mm.bing.net/th/id/OIP.cV8IfMXFn2uqn3YOR4ne0gHaHa?r=0&cb=thfc1falcon&w=1024&h=1024&rs=1&pid=ImgDetMain&o=7&rm=3' WHERE category_name = 'Breakfast' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://th.bing.com/th/id/R.4327e9e3d10634e6af86b81314bacd0d?rik=9VdoAm28zgLKsQ&pid=ImgRaw&r=0' WHERE category_name = 'Beverages' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://www.houseofyork.co.za/images/cmsimages/big/news-288-2588-walkie-talkie.jpeg' WHERE category_name = 'Smiley & Walkie Talkies' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://www.thesouthafrican.com/wp-content/uploads/2020/07/087f68fa-umgquasho-samp-and-beans-with-lamb-and-chakalaka.jpg' WHERE category_name = 'Umngqusho' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://healy-group.com/wp-content/uploads/AdobeStock_953274304-min-1920x1076.jpeg' WHERE category_name = 'Snacks & Sides' AND default_image IS NULL;
UPDATE categories SET default_image = 'https://as2.ftcdn.net/v2/jpg/02/23/81/47/1000_F_223814741_k90kjLiXIFbLXpUtlnlOWyioTUoMt1vU.jpg' WHERE category_name = 'Vetkoek' AND default_image IS NULL;
