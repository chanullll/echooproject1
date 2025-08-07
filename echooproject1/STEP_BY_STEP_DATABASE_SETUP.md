# EcoStore Database Setup - Step by Step Guide

## Prerequisites
- PostgreSQL installed and running
- Database management tool (pgAdmin, DBeaver, phpPgAdmin, or command line)
- Basic knowledge of SQL execution

## Step 1: Prepare Your Environment

### 1.1 Check PostgreSQL Installation
Open your command prompt/terminal and run:
```bash
psql --version
```
If you see a version number, PostgreSQL is installed. If not, download from: https://www.postgresql.org/download/

### 1.2 Start PostgreSQL Service
- **Windows**: Services → PostgreSQL → Start
- **Mac**: `brew services start postgresql`
- **Linux**: `sudo systemctl start postgresql`

## Step 2: Connect to PostgreSQL

### Option A: Using pgAdmin (Recommended for beginners)
1. Open pgAdmin
2. Connect to your PostgreSQL server
3. Right-click on "Databases" → Create → Database
4. Name: `ecostore`
5. Click "Save"

### Option B: Using Command Line
```bash
psql -U postgres
CREATE DATABASE ecostore;
\q
```

### Option C: Using Other SQL Editors
1. Open your SQL editor (DBeaver, phpPgAdmin, etc.)
2. Connect to PostgreSQL server
3. Execute: `CREATE DATABASE ecostore;`

## Step 3: Update Database Configuration

Edit the file `config/database.php` and update your password:
```php
$password = 'YOUR_ACTUAL_POSTGRESQL_PASSWORD';
```

## Step 4: Execute SQL Scripts in Order

**IMPORTANT**: Execute these scripts in the exact order shown below. Each script depends on the previous ones.

### Script 1: Create Users Table
```sql
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

-- Create indexes for better performance
CREATE INDEX idx_users_carbon_saved ON users(total_carbon_saved DESC);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
```

### Script 2: Create Categories Table
```sql
-- Categories table
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for better performance
CREATE INDEX idx_categories_name ON categories(name);
```

### Script 3: Create Products Table
```sql
-- Products table
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
);

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_products_approved ON products(is_approved);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_carbon_saved ON products(carbon_saved DESC);
CREATE INDEX idx_products_created_at ON products(created_at DESC);
```

### Script 4: Create Orders Table
```sql
-- Orders table
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    total_amount DECIMAL(10,2) NOT NULL,
    total_carbon_saved DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at DESC);
```

### Script 5: Create Order Items Table
```sql
-- Order items table
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    carbon_saved DECIMAL(8,2) DEFAULT 0.00
);

-- Create indexes for better performance
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
```

### Script 6: Create Cart Table
```sql
-- Cart table (for persistent cart storage)
CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);

-- Create indexes for better performance
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_cart_product ON cart(product_id);
CREATE INDEX idx_cart_created_at ON cart(created_at);
```

### Script 7: Create Badges Table
```sql
-- Badges table
CREATE TABLE badges (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    carbon_threshold DECIMAL(8,2) NOT NULL,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_badges_threshold ON badges(carbon_threshold);
CREATE INDEX idx_badges_name ON badges(name);
```

### Script 8: Create User Badges Table
```sql
-- User badges (many-to-many relationship)
CREATE TABLE user_badges (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    badge_id INTEGER REFERENCES badges(id) ON DELETE CASCADE,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, badge_id)
);

-- Create indexes for better performance
CREATE INDEX idx_user_badges_user ON user_badges(user_id);
CREATE INDEX idx_user_badges_badge ON user_badges(badge_id);
CREATE INDEX idx_user_badges_earned_at ON user_badges(earned_at DESC);
```

### Script 9: Create Wishlist Table
```sql
-- Wishlist table
CREATE TABLE wishlist (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, product_id)
);

-- Create indexes for better performance
CREATE INDEX idx_wishlist_user ON wishlist(user_id);
CREATE INDEX idx_wishlist_product ON wishlist(product_id);
CREATE INDEX idx_wishlist_created_at ON wishlist(created_at DESC);
```

### Script 10: Insert Sample Data
```sql
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
```

## Step 5: Verify Your Setup

### 5.1 Test Database Connection
Run this command in your project directory:
```bash
php database_connection_test.php
```

### 5.2 Check Tables Were Created
In your SQL editor, run:
```sql
SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';
```

You should see these 9 tables:
- users
- categories
- products
- orders
- order_items
- cart
- badges
- user_badges
- wishlist

### 5.3 Verify Sample Data
```sql
SELECT COUNT(*) as user_count FROM users;
SELECT COUNT(*) as product_count FROM products;
SELECT COUNT(*) as category_count FROM categories;
```

## Step 6: Test Your Application

1. Open your web browser
2. Navigate to your EcoStore application
3. Try logging in with demo accounts:
   - **Admin**: username: `admin`, password: `admin123`
   - **Seller**: username: `greenseller`, password: `seller123`
   - **Buyer**: username: `ecobuyer`, password: `buyer123`

## Troubleshooting

### Common Issues:

1. **"Database does not exist"**
   - Make sure you created the `ecostore` database first

2. **"Connection refused"**
   - Check if PostgreSQL service is running
   - Verify your username/password in `config/database.php`

3. **"Table already exists"**
   - If you need to start over, run: `DROP DATABASE ecostore;` then `CREATE DATABASE ecostore;`

4. **"Permission denied"**
   - Make sure your PostgreSQL user has CREATE privileges

### Getting Help:
- Check PostgreSQL logs for detailed error messages
- Verify your PHP has PDO PostgreSQL extension installed
- Ensure all scripts were executed in the correct order

## Success Indicators:
✅ Database `ecostore` created  
✅ All 9 tables created successfully  
✅ Sample data inserted  
✅ Connection test passes  
✅ Demo accounts work  
✅ Application loads without errors  

Congratulations! Your EcoStore database is now ready to use.