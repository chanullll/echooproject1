<?php
/**
 * Fix Table Structure
 * This will add missing columns to existing tables
 */

require_once 'config/database.php';

echo "<h1>Fix Table Structure</h1>";
echo "<hr>";

try {
    echo "<h2>Fixing Table Structures...</h2>";
    
    // Check and fix users table
    echo "<h3>1. Fixing users table...</h3>";
    
    // Get current columns in users table
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'users' AND table_schema = 'public'
    ");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Current columns in users: " . implode(', ', $existing_columns) . "</p>";
    
    // Add missing columns to users table
    $users_columns = [
        'username' => 'VARCHAR(50) UNIQUE',
        'email' => 'VARCHAR(100) UNIQUE',
        'password' => 'VARCHAR(255)',
        'role' => "VARCHAR(20) DEFAULT 'buyer'",
        'full_name' => 'VARCHAR(100)',
        'total_carbon_saved' => 'DECIMAL(10,2) DEFAULT 0.00',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    foreach ($users_columns as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✅ Added column '$column' to users table</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error adding column '$column': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>Column '$column' already exists in users table</p>";
        }
    }
    
    // Add constraints to users table
    try {
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('buyer', 'seller', 'admin'))");
        echo "<p style='color: green;'>✅ Added role constraint to users table</p>";
    } catch (Exception $e) {
        echo "<p>Note: Role constraint may already exist or failed: " . $e->getMessage() . "</p>";
    }
    
    // Check and fix products table
    echo "<h3>2. Fixing products table...</h3>";
    
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'products' AND table_schema = 'public'
    ");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Current columns in products: " . implode(', ', $existing_columns) . "</p>";
    
    // Add missing columns to products table
    $products_columns = [
        'name' => 'VARCHAR(100)',
        'description' => 'TEXT',
        'price' => 'DECIMAL(10,2)',
        'category_id' => 'INTEGER',
        'seller_id' => 'INTEGER',
        'carbon_saved' => 'DECIMAL(8,2) DEFAULT 0.00',
        'stock_quantity' => 'INTEGER DEFAULT 0',
        'image_url' => 'VARCHAR(255)',
        'is_approved' => 'BOOLEAN DEFAULT false',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];
    
    foreach ($products_columns as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $pdo->exec("ALTER TABLE products ADD COLUMN $column $definition");
                echo "<p style='color: green;'>✅ Added column '$column' to products table</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error adding column '$column': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>Column '$column' already exists in products table</p>";
        }
    }
    
    // Check and fix other tables
    $other_tables = [
        'categories' => [
            'name' => 'VARCHAR(50)',
            'description' => 'TEXT',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'orders' => [
            'user_id' => 'INTEGER',
            'total_amount' => 'DECIMAL(10,2)',
            'total_carbon_saved' => 'DECIMAL(10,2) DEFAULT 0.00',
            'status' => "VARCHAR(20) DEFAULT 'pending'",
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'cart' => [
            'user_id' => 'INTEGER',
            'product_id' => 'INTEGER',
            'quantity' => 'INTEGER DEFAULT 1',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ],
        'badges' => [
            'name' => 'VARCHAR(50)',
            'description' => 'TEXT',
            'carbon_threshold' => 'DECIMAL(8,2)',
            'icon' => 'VARCHAR(50)',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]
    ];
    
    foreach ($other_tables as $table => $columns) {
        echo "<h3>3. Fixing $table table...</h3>";
        
        $stmt = $pdo->query("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = '$table' AND table_schema = 'public'
        ");
        $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($columns as $column => $definition) {
            if (!in_array($column, $existing_columns)) {
                try {
                    $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
                    echo "<p style='color: green;'>✅ Added column '$column' to $table table</p>";
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error adding column '$column' to $table: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    echo "<h3>4. Adding sample data...</h3>";
    
    // Clear existing data and add proper sample data
    try {
        // Add demo users if they don't exist
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        
        $demo_users = [
            ['admin', 'admin@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', 0.00],
            ['greenseller', 'seller@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'Green Seller', 15.50],
            ['ecobuyer', 'buyer@ecostore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Eco Buyer', 32.75]
        ];
        
        foreach ($demo_users as $user) {
            $stmt->execute([$user[0]]);
            if ($stmt->fetch()['count'] == 0) {
                $insert_stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                if ($insert_stmt->execute($user)) {
                    echo "<p style='color: green;'>✅ Added demo user: {$user[0]}</p>";
                }
            } else {
                echo "<p>Demo user {$user[0]} already exists</p>";
            }
        }
        
        // Add categories if they don't exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        if ($stmt->fetch()['count'] == 0) {
            $pdo->exec("
                INSERT INTO categories (name, description) VALUES
                ('Clothing', 'Sustainable and eco-friendly clothing items'),
                ('Home & Garden', 'Eco-friendly products for home and garden'),
                ('Electronics', 'Energy-efficient and sustainable electronics'),
                ('Personal Care', 'Natural and organic personal care products'),
                ('Food & Beverages', 'Organic and locally sourced food items')
            ");
            echo "<p style='color: green;'>✅ Added sample categories</p>";
        }
        
        // Add badges if they don't exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM badges");
        if ($stmt->fetch()['count'] == 0) {
            $pdo->exec("
                INSERT INTO badges (name, description, carbon_threshold, icon) VALUES
                ('Green Beginner', 'Saved your first 5kg of CO2', 5.00, '🌱'),
                ('Eco Warrior', 'Saved 25kg of CO2', 25.00, '🌿'),
                ('Planet Protector', 'Saved 50kg of CO2', 50.00, '🌍'),
                ('Climate Champion', 'Saved 100kg of CO2', 100.00, '🏆'),
                ('Earth Guardian', 'Saved 250kg of CO2', 250.00, '🌟')
            ");
            echo "<p style='color: green;'>✅ Added sample badges</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error adding sample data: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Table Structure Fix Complete!</h2>";
    echo "<p>Now run the database test again to verify everything is working.</p>";
    echo "<p><a href='database_test.php'>Run Database Test</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error fixing table structure: " . $e->getMessage() . "</p>";
}
?>