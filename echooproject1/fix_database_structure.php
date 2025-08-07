<?php
/**
 * Fix Database Structure to Match PHP Application
 * This will update the database schema to match what the PHP code expects
 */

require_once 'config/database.php';

echo "<h1>Fixing Database Structure</h1>";
echo "<hr>";

try {
    echo "<h2>Updating Database Schema to Match PHP Code...</h2>";
    
    // Fix users table
    echo "<h3>1. Fixing users table...</h3>";
    
    // Add missing columns and rename existing ones
    $user_fixes = [
        // Add username column if it doesn't exist
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50)",
        // Add full_name column if it doesn't exist  
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(100)",
        // Add total_carbon_saved column if it doesn't exist
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS total_carbon_saved DECIMAL(10,2) DEFAULT 0.00",
        // Add created_at column if it doesn't exist
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        // Copy data from old columns to new ones
        "UPDATE users SET username = name WHERE username IS NULL AND name IS NOT NULL",
        "UPDATE users SET full_name = name WHERE full_name IS NULL AND name IS NOT NULL", 
        "UPDATE users SET total_carbon_saved = carbon_saved WHERE total_carbon_saved = 0 AND carbon_saved IS NOT NULL"
    ];
    
    foreach ($user_fixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Executed: " . substr($sql, 0, 50) . "...</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . substr($sql, 0, 50) . "... - " . $e->getMessage() . "</p>";
        }
    }
    
    // Add unique constraints
    try {
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_username_unique UNIQUE (username)");
        echo "<p style='color: green;'>✅ Added unique constraint for username</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Username unique constraint: " . $e->getMessage() . "</p>";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)");
        echo "<p style='color: green;'>✅ Added unique constraint for email</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Email unique constraint: " . $e->getMessage() . "</p>";
    }
    
    // Fix products table
    echo "<h3>2. Fixing products table...</h3>";
    
    $product_fixes = [
        // Add missing columns
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INTEGER",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS seller_id INTEGER", 
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS carbon_saved DECIMAL(8,2) DEFAULT 0.00",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS stock_quantity INTEGER DEFAULT 0",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS is_approved BOOLEAN DEFAULT false",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        // Copy data from old columns
        "UPDATE products SET stock_quantity = stock WHERE stock_quantity = 0 AND stock IS NOT NULL",
        "UPDATE products SET carbon_saved = carbon_impact WHERE carbon_saved = 0 AND carbon_impact IS NOT NULL"
    ];
    
    foreach ($product_fixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Executed: " . substr($sql, 0, 50) . "...</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . substr($sql, 0, 50) . "... - " . $e->getMessage() . "</p>";
        }
    }
    
    // Fix orders table
    echo "<h3>3. Fixing orders table...</h3>";
    
    $order_fixes = [
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2)",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS total_carbon_saved DECIMAL(10,2) DEFAULT 0.00",
        "UPDATE orders SET total_amount = total_price WHERE total_amount IS NULL AND total_price IS NOT NULL"
    ];
    
    foreach ($order_fixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Executed: " . substr($sql, 0, 50) . "...</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . substr($sql, 0, 50) . "... - " . $e->getMessage() . "</p>";
        }
    }
    
    // Add foreign key constraints
    echo "<h3>4. Adding foreign key constraints...</h3>";
    
    $constraints = [
        "ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL",
        "ALTER TABLE products ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE cart ADD CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE cart ADD CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE",
        "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE",
        "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE",
        "ALTER TABLE user_badges ADD CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE user_badges ADD CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE",
        "ALTER TABLE wishlist ADD CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE wishlist ADD CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE"
    ];
    
    foreach ($constraints as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Added constraint</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Constraint already exists or failed: " . $e->getMessage() . "</p>";
        }
    }
    
    // Update existing data to make it compatible
    echo "<h3>5. Updating existing data...</h3>";
    
    // Set default values for products
    try {
        $pdo->exec("UPDATE products SET category_id = 1 WHERE category_id IS NULL");
        $pdo->exec("UPDATE products SET seller_id = 2 WHERE seller_id IS NULL"); // Assuming greenseller has id 2
        $pdo->exec("UPDATE products SET is_approved = true WHERE is_approved IS NULL");
        echo "<p style='color: green;'>✅ Updated product default values</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Error updating products: " . $e->getMessage() . "</p>";
    }
    
    // Create indexes for better performance
    echo "<h3>6. Creating indexes...</h3>";
    
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_total_carbon_saved ON users(total_carbon_saved DESC)",
        "CREATE INDEX IF NOT EXISTS idx_products_category_id ON products(category_id)",
        "CREATE INDEX IF NOT EXISTS idx_products_seller_id ON products(seller_id)",
        "CREATE INDEX IF NOT EXISTS idx_products_is_approved ON products(is_approved)",
        "CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_cart_user_id ON cart(user_id)"
    ];
    
    foreach ($indexes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Created index</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Index creation: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>✅ Database Structure Fix Complete!</h2>";
    echo "<p>The database has been updated to match the PHP application requirements.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Run the database test again to verify the fixes</li>";
    echo "<li>Try logging in with the demo accounts</li>";
    echo "<li>Test the application functionality</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error fixing database structure: " . $e->getMessage() . "</p>";
}
?>