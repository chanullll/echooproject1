<?php
/**
 * Quick Setup Verification Script
 * Run this after completing the database setup to verify everything works
 */

require_once 'config/database.php';

echo "EcoStore Database Setup Verification\n";
echo "====================================\n\n";

try {
    // Test connection
    echo "1. Testing database connection... ";
    $pdo->query("SELECT 1");
    echo "✅ SUCCESS\n";
    
    // Check if all required tables exist
    echo "2. Checking required tables... ";
    $required_tables = [
        'users', 'categories', 'products', 'orders', 
        'order_items', 'cart', 'badges', 'user_badges', 'wishlist'
    ];
    
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
    ");
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    
    if (empty($missing_tables)) {
        echo "✅ SUCCESS (All 9 tables found)\n";
    } else {
        echo "❌ MISSING TABLES: " . implode(', ', $missing_tables) . "\n";
        exit(1);
    }
    
    // Check sample data
    echo "3. Verifying sample data... ";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $user_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $category_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $product_count = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM badges");
    $badge_count = $stmt->fetch()['count'];
    
    if ($user_count >= 3 && $category_count >= 5 && $product_count >= 6 && $badge_count >= 5) {
        echo "✅ SUCCESS\n";
        echo "   - Users: $user_count\n";
        echo "   - Categories: $category_count\n";
        echo "   - Products: $product_count\n";
        echo "   - Badges: $badge_count\n";
    } else {
        echo "❌ INSUFFICIENT DATA\n";
        echo "   - Users: $user_count (expected: 3+)\n";
        echo "   - Categories: $category_count (expected: 5+)\n";
        echo "   - Products: $product_count (expected: 6+)\n";
        echo "   - Badges: $badge_count (expected: 5+)\n";
        exit(1);
    }
    
    // Test demo accounts
    echo "4. Testing demo accounts... ";
    
    $demo_accounts = [
        'admin' => 'admin',
        'greenseller' => 'seller', 
        'ecobuyer' => 'buyer'
    ];
    
    $all_accounts_exist = true;
    foreach ($demo_accounts as $username => $expected_role) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user || $user['role'] !== $expected_role) {
            $all_accounts_exist = false;
            break;
        }
    }
    
    if ($all_accounts_exist) {
        echo "✅ SUCCESS\n";
    } else {
        echo "❌ DEMO ACCOUNTS MISSING OR INCORRECT\n";
        exit(1);
    }
    
    // Test indexes
    echo "5. Checking database indexes... ";
    
    $stmt = $pdo->query("
        SELECT COUNT(*) as index_count 
        FROM pg_indexes 
        WHERE schemaname = 'public'
    ");
    $index_count = $stmt->fetch()['index_count'];
    
    if ($index_count >= 15) {
        echo "✅ SUCCESS ($index_count indexes found)\n";
    } else {
        echo "⚠️  WARNING: Only $index_count indexes found (expected 15+)\n";
    }
    
    echo "\n🎉 DATABASE SETUP COMPLETE! 🎉\n";
    echo "=====================================\n";
    echo "Your EcoStore database is ready to use.\n\n";
    
    echo "Demo Accounts:\n";
    echo "- Admin: username 'admin', password 'admin123'\n";
    echo "- Seller: username 'greenseller', password 'seller123'\n";
    echo "- Buyer: username 'ecobuyer', password 'buyer123'\n\n";
    
    echo "Next steps:\n";
    echo "1. Start your web server (XAMPP, WAMP, etc.)\n";
    echo "2. Navigate to your EcoStore application\n";
    echo "3. Try logging in with the demo accounts\n";
    echo "4. Explore the features!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure PostgreSQL is running\n";
    echo "2. Check your database credentials in config/database.php\n";
    echo "3. Ensure all SQL scripts were executed successfully\n";
    echo "4. Verify the 'ecostore' database exists\n";
    exit(1);
}
?>