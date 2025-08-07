<?php
/**
 * Database Setup Script
 * Run this file once to set up the database with sample data
 */

require_once 'config/database.php';

try {
    echo "Setting up EcoStore database...\n";
    
    // Check if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found. Please run the SQL migration file first.\n";
        echo "You can find it at: supabase/migrations/20250728155417_lively_tower.sql\n";
        exit(1);
    }
    
    echo "Database tables found: " . implode(', ', $tables) . "\n";
    
    // Test basic queries
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "Users in database: $userCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    echo "Products in database: $productCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $stmt->fetch()['count'];
    echo "Categories in database: $categoryCount\n";
    
    echo "Database setup verification complete!\n";
    echo "You can now use the EcoStore application.\n";
    
} catch (Exception $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and ensure PostgreSQL is running.\n";
    exit(1);
}
?>