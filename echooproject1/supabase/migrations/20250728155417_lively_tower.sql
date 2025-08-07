-- EcoStore Database Schema
-- PostgreSQL Database Setup

-- Create database (run this command separately in psql)
-- CREATE DATABASE ecostore;

-- Users table with role-based access
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'buyer' CHECK (role IN ('buyer', 'seller', 'admin')),
    full_name VARCHAR(100) NOT NULL,
    total_carbon_saved DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INTEGER REFERENCES categories(id),
    seller_id INTEGER REFERENCES users(id),
    carbon_saved DECIMAL(8,2) DEFAULT 0.00,
    stock_quantity INTEGER DEFAULT 0,
    image_url VARCHAR(255),
    is_approved BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    total_amount DECIMAL(10,2) NOT NULL,
    total_carbon_saved DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id),
    product_id INTEGER REFERENCES products(id),
    quantity INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    carbon_saved DECIMAL(8,2) DEFAULT 0.00
);

-- Cart table (for persistent cart storage)
CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    product_id INTEGER REFERENCES products(id),
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);

-- Badges table
CREATE TABLE badges (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    carbon_threshold DECIMAL(8,2) NOT NULL,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User badges (many-to-many relationship)
CREATE TABLE user_badges (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    badge_id INTEGER REFERENCES badges(id),
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, badge_id)
);

-- Wishlist table
CREATE TABLE wishlist (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    product_id INTEGER REFERENCES products(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Clothing', 'Sustainable and eco-friendly clothing items'),
('Home & Garden', 'Eco-friendly products for home and garden'),
('Electronics', 'Energy-efficient and sustainable electronics'),
('Personal Care', 'Natural and organic personal care products'),
('Food & Beverages', 'Organic and locally sourced food items');

-- Insert sample badges
INSERT INTO badges (name, description, carbon_threshold, icon) VALUES
('Green Beginner', 'Saved your first 5kg of CO2', 5.00, '🌱'),
('Eco Warrior', 'Saved 25kg of CO2', 25.00, '🌿'),
('Planet Protector', 'Saved 50kg of CO2', 50.00, '🌍'),
('Climate Champion', 'Saved 100kg of CO2', 100.00, '🏆'),
('Earth Guardian', 'Saved 250kg of CO2', 250.00, '🌟');

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password, role, full_name) VALUES
('admin', 'admin@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User');

-- Insert sample seller (password: seller123)
INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) VALUES
('greenseller', 'seller@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'Green Seller', 15.50);

-- Insert sample buyer (password: buyer123)
INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) VALUES
('ecobuyer', 'buyer@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Eco Buyer', 32.75);

-- Insert sample products
INSERT INTO products (name, description, price, category_id, seller_id, carbon_saved, stock_quantity, image_url, is_approved) VALUES
('Organic Cotton T-Shirt', 'Made from 100% organic cotton, reducing water usage by 91%', 29.99, 1, 2, 2.5, 50, 'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg', true),
('Bamboo Toothbrush Set', 'Biodegradable bamboo toothbrushes, pack of 4', 12.99, 4, 2, 0.8, 100, 'https://images.pexels.com/photos/4465124/pexels-photo-4465124.jpeg', true),
('Solar Power Bank', 'Portable solar charger with 10000mAh capacity', 45.99, 3, 2, 5.2, 25, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg', true),
('Reusable Water Bottle', 'Stainless steel bottle that eliminates plastic waste', 19.99, 2, 2, 1.2, 75, 'https://images.pexels.com/photos/3737579/pexels-photo-3737579.jpeg', true),
('Organic Quinoa', 'Locally sourced organic quinoa, 1kg pack', 8.99, 5, 2, 0.9, 40, 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg', true),
('LED Light Bulbs', 'Energy-efficient LED bulbs, pack of 6', 24.99, 2, 2, 3.8, 60, 'https://images.pexels.com/photos/1112598/pexels-photo-1112598.jpeg', true);

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_users_carbon_saved ON users(total_carbon_saved DESC);