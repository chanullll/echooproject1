<?php
/**
 * Database Connection and Table Test
 * Run this to test your database connection and verify tables exist
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>EcoStore Database Test</h1>";
echo "<hr>";

try {
    // Test basic connection
    echo "<h2>1. Database Connection Test</h2>";
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetch();
    echo "<p style='color: green;'>✅ Connected successfully!</p>";
    echo "<p>PostgreSQL Version: " . htmlspecialchars($version['version']) . "</p>";
    
    // Check database name
    $stmt = $pdo->query("SELECT current_database()");
    $db = $stmt->fetch();
    echo "<p>Database: " . htmlspecialchars($db['current_database']) . "</p>";
    
    echo "<h2>2. Table Structure Test</h2>";
    
    // Check if all required tables exist
    $required_tables = [
        'users', 'categories', 'products', 'orders', 
        'order_items', 'cart', 'badges', 'user_badges', 'wishlist'
    ];
    
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Found tables: " . implode(', ', $existing_tables) . "</p>";
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    
    if (empty($missing_tables)) {
        echo "<p style='color: green;'>✅ All required tables exist!</p>";
    } else {
        echo "<p style='color: red;'>❌ Missing tables: " . implode(', ', $missing_tables) . "</p>";
        echo "<p>Please run the SQL migration files to create missing tables.</p>";
    }
    
    echo "<h2>3. Sample Data Test</h2>";
    
    // Test each table for data
    foreach ($existing_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "<p>$table: $count records</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error querying $table: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>4. User Authentication Test</h2>";
    
    // Test demo accounts
    $demo_accounts = [
        'admin' => 'admin',
        'greenseller' => 'seller', 
        'ecobuyer' => 'buyer'
    ];
    
    foreach ($demo_accounts as $username => $expected_role) {
        try {
            $stmt = $pdo->prepare("SELECT username, role, total_carbon_saved FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "<p style='color: green;'>✅ $username exists (role: {$user['role']}, carbon: {$user['total_carbon_saved']} kg)</p>";
            } else {
                echo "<p style='color: red;'>❌ $username not found</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error checking $username: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>5. Leaderboard Test</h2>";
    
    // Test leaderboard query
    try {
        $stmt = $pdo->query("
            SELECT u.username, u.total_carbon_saved, u.role,
                   ROW_NUMBER() OVER (ORDER BY u.total_carbon_saved DESC) as rank
            FROM users u 
            WHERE u.role != 'admin' AND u.total_carbon_saved > 0
            ORDER BY u.total_carbon_saved DESC 
            LIMIT 10
        ");
        $leaderboard = $stmt->fetchAll();
        
        if (!empty($leaderboard)) {
            echo "<p style='color: green;'>✅ Leaderboard query works!</p>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Rank</th><th>Username</th><th>Role</th><th>CO₂ Saved</th></tr>";
            foreach ($leaderboard as $user) {
                echo "<tr>";
                echo "<td>{$user['rank']}</td>";
                echo "<td>{$user['username']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td>{$user['total_carbon_saved']} kg</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No users with carbon savings found for leaderboard</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Leaderboard query failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>6. Products Test</h2>";
    
    // Test products query
    try {
        $stmt = $pdo->query("
            SELECT p.name, p.price, p.carbon_saved, c.name as category, u.username as seller
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            JOIN users u ON p.seller_id = u.id 
            WHERE p.is_approved = true 
            LIMIT 5
        ");
        $products = $stmt->fetchAll();
        
        if (!empty($products)) {
            echo "<p style='color: green;'>✅ Products query works!</p>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Product</th><th>Price</th><th>CO₂ Saved</th><th>Category</th><th>Seller</th></tr>";
            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>{$product['name']}</td>";
                echo "<td>$" . number_format($product['price'], 2) . "</td>";
                echo "<td>{$product['carbon_saved']} kg</td>";
                echo "<td>{$product['category']}</td>";
                echo "<td>{$product['seller']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ No approved products found</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Products query failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<h2>Summary</h2>";
    echo "<p>If all tests show green checkmarks, your EcoStore database is working correctly!</p>";
    echo "<p><strong>Demo Accounts:</strong></p>";
    echo "<ul>";
    echo "<li>Admin: username 'admin', password 'admin123'</li>";
    echo "<li>Seller: username 'greenseller', password 'seller123'</li>";
    echo "<li>Buyer: username 'ecobuyer', password 'buyer123'</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>PostgreSQL is running</li>";
    echo "<li>Database 'echostore' exists</li>";
    echo "<li>Username and password are correct</li>";
    echo "<li>PHP PDO PostgreSQL extension is installed</li>";
    echo "</ul>";
}
?>