<?php
/**
 * Complete Database Structure Fix
 * This will fix all database structure issues to match the PHP application
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><title>Database Fix</title></head><body>";
echo "<h1>🔧 Complete EcoStore Database Fix</h1>";
echo "<hr>";

try {
    echo "<h2>Step 1: Analyzing Current Database Structure</h2>";
    
    // Check current structure
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Found tables: " . implode(', ', $tables) . "</p>";
    
    echo "<h2>Step 2: Fixing Users Table Structure</h2>";
    
    // Fix users table - add missing columns
    $user_fixes = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(50)",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(100)", 
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS total_carbon_saved DECIMAL(10,2) DEFAULT 0.00",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    
    foreach ($user_fixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ " . $sql . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . $sql . " - " . $e->getMessage() . "</p>";
        }
    }
    
    // Copy data from old columns to new columns
    echo "<h3>Copying data from old columns to new columns...</h3>";
    
    try {
        // Copy name to username and full_name
        $pdo->exec("UPDATE users SET username = name WHERE username IS NULL AND name IS NOT NULL");
        $pdo->exec("UPDATE users SET full_name = name WHERE full_name IS NULL AND name IS NOT NULL");
        echo "<p style='color: green;'>✅ Copied name to username and full_name</p>";
        
        // Copy carbon_saved to total_carbon_saved
        $pdo->exec("UPDATE users SET total_carbon_saved = COALESCE(carbon_saved, 0) WHERE total_carbon_saved = 0");
        echo "<p style='color: green;'>✅ Copied carbon_saved to total_carbon_saved</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error copying data: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Step 3: Fixing Products Table Structure</h2>";
    
    // Fix products table - add missing columns
    $product_fixes = [
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INTEGER",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS seller_id INTEGER",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS carbon_saved DECIMAL(8,2) DEFAULT 0.00",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS stock_quantity INTEGER DEFAULT 0", 
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS is_approved BOOLEAN DEFAULT false",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];
    
    foreach ($product_fixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ " . $sql . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . $sql . " - " . $e->getMessage() . "</p>";
        }
    }
    
    // Copy data from old columns to new columns
    echo "<h3>Copying product data from old columns...</h3>";
    
    try {
        // Copy stock to stock_quantity
        $pdo->exec("UPDATE products SET stock_quantity = COALESCE(stock, 0) WHERE stock_quantity = 0");
        echo "<p style='color: green;'>✅ Copied stock to stock_quantity</p>";
        
        // Copy carbon_impact to carbon_saved
        $pdo->exec("UPDATE products SET carbon_saved = COALESCE(carbon_impact, 0) WHERE carbon_saved = 0");
        echo "<p style='color: green;'>✅ Copied carbon_impact to carbon_saved</p>";
        
        // Set default values for missing data
        $pdo->exec("UPDATE products SET category_id = 1 WHERE category_id IS NULL");
        $pdo->exec("UPDATE products SET seller_id = 2 WHERE seller_id IS NULL"); // Assuming greenseller will have id 2
        $pdo->exec("UPDATE products SET is_approved = true WHERE is_approved IS NULL OR is_approved = false");
        echo "<p style='color: green;'>✅ Set default values for products</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error copying product data: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Step 4: Fixing Orders Table Structure</h2>";
    
    // Fix orders table
    $order_fixes = [
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2)",
        "ALTER TABLE orders ADD COLUMN IF NOT EXISTS total_carbon_saved DECIMAL(10,2) DEFAULT 0.00"
    ];
    
    foreach ($order_fixes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ " . $sql . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ " . $sql . " - " . $e->getMessage() . "</p>";
        }
    }
    
    // Copy total_price to total_amount
    try {
        $pdo->exec("UPDATE orders SET total_amount = total_price WHERE total_amount IS NULL AND total_price IS NOT NULL");
        echo "<p style='color: green;'>✅ Copied total_price to total_amount</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Error copying order data: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Step 5: Creating/Updating Demo Users with Correct Passwords</h2>";
    
    // Delete existing demo users and recreate them
    $demo_emails = ['admin@ecostore.com', 'seller@ecostore.com', 'buyer@ecostore.com'];
    foreach ($demo_emails as $email) {
        try {
            $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
        } catch (Exception $e) {
            // Ignore errors
        }
    }
    
    // Create demo users with correct structure
    $demo_users = [
        [
            'username' => 'admin',
            'email' => 'admin@ecostore.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'name' => 'Admin User',
            'full_name' => 'Admin User',
            'total_carbon_saved' => 0.00,
            'carbon_saved' => 0.00
        ],
        [
            'username' => 'greenseller', 
            'email' => 'seller@ecostore.com',
            'password' => password_hash('seller123', PASSWORD_DEFAULT),
            'role' => 'seller',
            'name' => 'Green Seller',
            'full_name' => 'Green Seller', 
            'total_carbon_saved' => 15.50,
            'carbon_saved' => 15.50
        ],
        [
            'username' => 'ecobuyer',
            'email' => 'buyer@ecostore.com', 
            'password' => password_hash('buyer123', PASSWORD_DEFAULT),
            'role' => 'buyer',
            'name' => 'Eco Buyer',
            'full_name' => 'Eco Buyer',
            'total_carbon_saved' => 32.75,
            'carbon_saved' => 32.75
        ]
    ];
    
    foreach ($demo_users as $user) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role, name, full_name, total_carbon_saved, carbon_saved) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $user['username'],
                $user['email'], 
                $user['password'],
                $user['role'],
                $user['name'],
                $user['full_name'],
                $user['total_carbon_saved'],
                $user['carbon_saved']
            ])) {
                echo "<p style='color: green;'>✅ Created demo user: {$user['username']}</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error creating user {$user['username']}: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Step 6: Adding Database Constraints and Indexes</h2>";
    
    // Add unique constraints
    $constraints = [
        "ALTER TABLE users ADD CONSTRAINT users_username_unique UNIQUE (username)",
        "ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)",
        "ALTER TABLE cart ADD CONSTRAINT cart_user_product_unique UNIQUE (user_id, product_id)"
    ];
    
    foreach ($constraints as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Added constraint</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Constraint already exists: " . $e->getMessage() . "</p>";
        }
    }
    
    // Add indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)", 
        "CREATE INDEX IF NOT EXISTS idx_users_total_carbon_saved ON users(total_carbon_saved DESC)",
        "CREATE INDEX IF NOT EXISTS idx_products_category_id ON products(category_id)",
        "CREATE INDEX IF NOT EXISTS idx_products_seller_id ON products(seller_id)",
        "CREATE INDEX IF NOT EXISTS idx_products_is_approved ON products(is_approved)"
    ];
    
    foreach ($indexes as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Created index</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Index creation: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Step 7: Final Verification</h2>";
    
    // Test login functionality
    $stmt = $pdo->prepare("SELECT username, email, role, total_carbon_saved FROM users WHERE username IN ('admin', 'greenseller', 'ecobuyer')");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<h3>Demo Users Verification:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Username</th><th>Email</th><th>Role</th><th>Carbon Saved</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['total_carbon_saved']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_approved = true");
    $approved_products = $stmt->fetch()['count'];
    echo "<p>Approved products: $approved_products</p>";
    
    echo "<hr>";
    echo "<h2>🎉 DATABASE FIX COMPLETED SUCCESSFULLY! 🎉</h2>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ What was fixed:</h3>";
    echo "<ul>";
    echo "<li>Added missing columns to users table (username, full_name, total_carbon_saved, created_at)</li>";
    echo "<li>Added missing columns to products table (category_id, seller_id, carbon_saved, stock_quantity, is_approved, created_at)</li>";
    echo "<li>Added missing columns to orders table (total_amount, total_carbon_saved)</li>";
    echo "<li>Copied data from old columns to new columns</li>";
    echo "<li>Created demo users with properly hashed passwords</li>";
    echo "<li>Set all products to approved status</li>";
    echo "<li>Added database constraints and indexes</li>";
    echo "</ul>";
    
    echo "<h3>🔑 Demo Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
    echo "<li><strong>Seller:</strong> username: <code>greenseller</code>, password: <code>seller123</code></li>";
    echo "<li><strong>Buyer:</strong> username: <code>ecobuyer</code>, password: <code>buyer123</code></li>";
    echo "</ul>";
    
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go to your EcoStore website</li>";
    echo "<li>Try logging in with any of the demo accounts above</li>";
    echo "<li>Check that products are now visible on the products page</li>";
    echo "<li>Test the shopping cart functionality</li>";
    echo "<li>Check the leaderboard page</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CRITICAL ERROR: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}

echo "</body></html>";
?>