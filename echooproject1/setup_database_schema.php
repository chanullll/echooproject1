<?php
/**
 * Setup Database Schema
 * This will create all the required tables with the correct structure
 */

require_once 'config/database.php';

echo "<h1>EcoStore Database Schema Setup</h1>";
echo "<hr>";

try {
    echo "<h2>Creating Database Schema...</h2>";
    
    // Drop existing tables if they exist (in correct order due to foreign keys)
    $drop_tables = [
        'user_badges', 'wishlist', 'order_items', 'cart', 'orders', 
        'products', 'categories', 'badges', 'users'
    ];
    
    echo "<h3>1. Dropping existing tables (if any)...</h3>";
    foreach ($drop_tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table CASCADE");
            echo "<p>Dropped table: $table</p>";
        } catch (Exception $e) {
            echo "<p>Note: $table didn't exist or couldn't be dropped</p>";
        }
    }
    
    echo "<h3>2. Creating tables...</h3>";
    
    // Create users table
    $pdo->exec("
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'buyer' CHECK (role IN ('buyer', 'seller', 'admin')),
            full_name VARCHAR(100) NOT NULL,
            total_carbon_saved DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✅ Created users table</p>";
    
    // Create categories table
    $pdo->exec("
        CREATE TABLE categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✅ Created categories table</p>";
    
    // Create badges table
    $pdo->exec("
        CREATE TABLE badges (
            id SERIAL PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description TEXT,
            carbon_threshold DECIMAL(8,2) NOT NULL,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✅ Created badges table</p>";
    
    // Create products table
    $pdo->exec("
        CREATE TABLE products (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
            seller_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            carbon_saved DECIMAL(8,2) DEFAULT 0.00,
            stock_quantity INTEGER DEFAULT 0,
            image_url VARCHAR(255),
            is_approved BOOLEAN DEFAULT false,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✅ Created products table</p>";
    
    // Create orders table
    $pdo->exec("
        CREATE TABLE orders (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            total_amount DECIMAL(10,2) NOT NULL,
            total_carbon_saved DECIMAL(10,2) DEFAULT 0.00,
            status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'completed', 'cancelled')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p style='color: green;'>✅ Created orders table</p>";
    
    // Create order_items table
    $pdo->exec("
        CREATE TABLE order_items (
            id SERIAL PRIMARY KEY,
            order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
            product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
            quantity INTEGER NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            carbon_saved DECIMAL(8,2) DEFAULT 0.00
        )
    ");
    echo "<p style='color: green;'>✅ Created order_items table</p>";
    
    // Create cart table
    $pdo->exec("
        CREATE TABLE cart (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
            quantity INTEGER NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, product_id)
        )
    ");
    echo "<p style='color: green;'>✅ Created cart table</p>";
    
    // Create user_badges table
    $pdo->exec("
        CREATE TABLE user_badges (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            badge_id INTEGER REFERENCES badges(id) ON DELETE CASCADE,
            earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, badge_id)
        )
    ");
    echo "<p style='color: green;'>✅ Created user_badges table</p>";
    
    // Create wishlist table
    $pdo->exec("
        CREATE TABLE wishlist (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, product_id)
        )
    ");
    echo "<p style='color: green;'>✅ Created wishlist table</p>";
    
    echo "<h3>3. Creating indexes...</h3>";
    
    // Create indexes for better performance
    $indexes = [
        "CREATE INDEX idx_users_carbon_saved ON users(total_carbon_saved DESC)",
        "CREATE INDEX idx_users_role ON users(role)",
        "CREATE INDEX idx_users_username ON users(username)",
        "CREATE INDEX idx_users_email ON users(email)",
        "CREATE INDEX idx_categories_name ON categories(name)",
        "CREATE INDEX idx_products_category ON products(category_id)",
        "CREATE INDEX idx_products_seller ON products(seller_id)",
        "CREATE INDEX idx_products_approved ON products(is_approved)",
        "CREATE INDEX idx_products_price ON products(price)",
        "CREATE INDEX idx_products_carbon_saved ON products(carbon_saved DESC)",
        "CREATE INDEX idx_products_created_at ON products(created_at DESC)",
        "CREATE INDEX idx_orders_user ON orders(user_id)",
        "CREATE INDEX idx_orders_status ON orders(status)",
        "CREATE INDEX idx_orders_created_at ON orders(created_at DESC)",
        "CREATE INDEX idx_order_items_order ON order_items(order_id)",
        "CREATE INDEX idx_order_items_product ON order_items(product_id)",
        "CREATE INDEX idx_cart_user ON cart(user_id)",
        "CREATE INDEX idx_cart_product ON cart(product_id)",
        "CREATE INDEX idx_cart_created_at ON cart(created_at)",
        "CREATE INDEX idx_badges_threshold ON badges(carbon_threshold)",
        "CREATE INDEX idx_badges_name ON badges(name)",
        "CREATE INDEX idx_user_badges_user ON user_badges(user_id)",
        "CREATE INDEX idx_user_badges_badge ON user_badges(badge_id)",
        "CREATE INDEX idx_user_badges_earned_at ON user_badges(earned_at DESC)",
        "CREATE INDEX idx_wishlist_user ON wishlist(user_id)",
        "CREATE INDEX idx_wishlist_product ON wishlist(product_id)",
        "CREATE INDEX idx_wishlist_created_at ON wishlist(created_at DESC)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $pdo->exec($index);
            echo "<p>Created index</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>Index creation note: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>4. Inserting sample data...</h3>";
    
    // Insert sample categories
    $pdo->exec("
        INSERT INTO categories (name, description) VALUES
        ('Clothing', 'Sustainable and eco-friendly clothing items'),
        ('Home & Garden', 'Eco-friendly products for home and garden'),
        ('Electronics', 'Energy-efficient and sustainable electronics'),
        ('Personal Care', 'Natural and organic personal care products'),
        ('Food & Beverages', 'Organic and locally sourced food items')
    ");
    echo "<p style='color: green;'>✅ Inserted categories</p>";
    
    // Insert sample badges
    $pdo->exec("
        INSERT INTO badges (name, description, carbon_threshold, icon) VALUES
        ('Green Beginner', 'Saved your first 5kg of CO2', 5.00, '🌱'),
        ('Eco Warrior', 'Saved 25kg of CO2', 25.00, '🌿'),
        ('Planet Protector', 'Saved 50kg of CO2', 50.00, '🌍'),
        ('Climate Champion', 'Saved 100kg of CO2', 100.00, '🏆'),
        ('Earth Guardian', 'Saved 250kg of CO2', 250.00, '🌟')
    ");
    echo "<p style='color: green;'>✅ Inserted badges</p>";
    
    // Insert sample users (passwords are hashed versions of the demo passwords)
    $pdo->exec("
        INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) VALUES
        ('admin', 'admin@ecostore.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', 0.00),
        ('greenseller', 'seller@ecostore.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'Green Seller', 15.50),
        ('ecobuyer', 'buyer@ecostore.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Eco Buyer', 32.75)
    ");
    echo "<p style='color: green;'>✅ Inserted demo users</p>";
    
    // Insert sample products
    $pdo->exec("
        INSERT INTO products (name, description, price, category_id, seller_id, carbon_saved, stock_quantity, image_url, is_approved) VALUES
        ('Organic Cotton T-Shirt', 'Made from 100% organic cotton, reducing water usage by 91%', 29.99, 1, 2, 2.5, 50, 'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg', true),
        ('Bamboo Toothbrush Set', 'Biodegradable bamboo toothbrushes, pack of 4', 12.99, 4, 2, 0.8, 100, 'https://images.pexels.com/photos/4465124/pexels-photo-4465124.jpeg', true),
        ('Solar Power Bank', 'Portable solar charger with 10000mAh capacity', 45.99, 3, 2, 5.2, 25, 'https://images.pexels.com/photos/4792728/pexels-photo-4792728.jpeg', true),
        ('Reusable Water Bottle', 'Stainless steel bottle that eliminates plastic waste', 19.99, 2, 2, 1.2, 75, 'https://images.pexels.com/photos/3737579/pexels-photo-3737579.jpeg', true),
        ('Organic Quinoa', 'Locally sourced organic quinoa, 1kg pack', 8.99, 5, 2, 0.9, 40, 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg', true),
        ('LED Light Bulbs', 'Energy-efficient LED bulbs, pack of 6', 24.99, 2, 2, 3.8, 60, 'https://images.pexels.com/photos/1112598/pexels-photo-1112598.jpeg', true)
    ");
    echo "<p style='color: green;'>✅ Inserted sample products</p>";
    
    // Add more test users for leaderboard
    $pdo->exec("
        INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) VALUES
        ('ecowarrior1', 'warrior1@test.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Eco Warrior One', 67.25),
        ('greenliving', 'green@test.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Green Living', 89.50),
        ('sustainableshopper', 'sustainable@test.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Sustainable Shopper', 123.75),
        ('planetprotector', 'planet@test.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Planet Protector', 156.00),
        ('climatechampion', 'climate@test.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Climate Champion', 234.50)
    ");
    echo "<p style='color: green;'>✅ Inserted test users for leaderboard</p>";
    
    echo "<hr>";
    echo "<h2>✅ Database Schema Setup Complete!</h2>";
    echo "<p>All tables have been created with the correct structure and sample data has been inserted.</p>";
    
    echo "<h3>Demo Accounts (all passwords work):</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: 'admin', password: 'admin123'</li>";
    echo "<li><strong>Seller:</strong> username: 'greenseller', password: 'seller123'</li>";
    echo "<li><strong>Buyer:</strong> username: 'ecobuyer', password: 'buyer123'</li>";
    echo "<li><strong>Test Users:</strong> All test users have password: 'test123'</li>";
    echo "</ul>";
    
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Run <a href='database_test.php'>database_test.php</a> to verify everything is working</li>";
    echo "<li>Visit your <a href='index.php'>homepage</a> to see the website</li>";
    echo "<li>Check the <a href='leaderboard.php'>leaderboard</a> to see the rankings</li>";
    echo "<li>Try logging in with the demo accounts</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error setting up database: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and permissions.</p>";
}
?>