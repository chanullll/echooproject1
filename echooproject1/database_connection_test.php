<?php
/**
 * Database Connection Test Script
 * Run this to verify your database connection is working
 */

// Database configuration - UPDATE THESE VALUES
$host = 'localhost';
$dbname = 'echostore';
$username = 'postgres';  // Change this to your PostgreSQL username
$password = '1234';      // Change this to your PostgreSQL password
$port = '5432';

echo "Testing EcoStore Database Connection...\n";
echo "=====================================\n";

try {
    // Test connection
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_PERSISTENT => false
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ Database connection successful!\n";
    echo "Host: $host\n";
    echo "Database: $dbname\n";
    echo "Port: $port\n\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetch();
    echo "PostgreSQL Version: " . $version['version'] . "\n\n";
    
    // Check if tables exist
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  No tables found. Please run the SQL scripts to create tables.\n";
    } else {
        echo "📋 Found tables:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
        
        // Check sample data
        if (in_array('users', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            echo "\n👥 Users in database: $userCount\n";
        }
        
        if (in_array('products', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
            $productCount = $stmt->fetch()['count'];
            echo "📦 Products in database: $productCount\n";
        }
    }
    
    echo "\n✅ Database setup verification complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Common solutions:\n";
    echo "1. Check if PostgreSQL is running\n";
    echo "2. Verify database credentials in config/database.php\n";
    echo "3. Ensure the 'ecostore' database exists\n";
    echo "4. Check if PHP has PDO PostgreSQL extension installed\n";
    
    exit(1);
}
?>