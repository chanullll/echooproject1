<?php
/**
 * Check Actual Column Structure
 * This will show us exactly what columns exist in each table
 */

require_once 'config/database.php';

echo "<h1>Actual Database Column Structure</h1>";
echo "<hr>";

try {
    // Check connection
    echo "<h2>1. Connection Test</h2>";
    $stmt = $pdo->query("SELECT current_database(), version()");
    $info = $stmt->fetch();
    echo "<p style='color: green;'>✅ Connected to database: " . $info['current_database'] . "</p>";
    
    // List all tables
    echo "<h2>2. Table and Column Analysis</h2>";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "<h3>Table: $table</h3>";
        try {
            $stmt = $pdo->query("
                SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns 
                WHERE table_name = '$table' AND table_schema = 'public'
                ORDER BY ordinal_position
            ");
            $columns = $stmt->fetchAll();
            
            if (!empty($columns)) {
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
                echo "<tr style='background-color: #f0f0f0;'><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
                foreach ($columns as $col) {
                    echo "<tr>";
                    echo "<td><strong>{$col['column_name']}</strong></td>";
                    echo "<td>{$col['data_type']}</td>";
                    echo "<td>{$col['is_nullable']}</td>";
                    echo "<td>" . ($col['column_default'] ?: 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Check for missing essential columns
                $column_names = array_column($columns, 'column_name');
                
                if ($table === 'users') {
                    $required = ['id', 'username', 'email', 'password', 'role', 'full_name', 'total_carbon_saved', 'created_at'];
                    $missing = array_diff($required, $column_names);
                    if (!empty($missing)) {
                        echo "<p style='color: red;'>❌ Missing columns in users: " . implode(', ', $missing) . "</p>";
                    } else {
                        echo "<p style='color: green;'>✅ Users table has all required columns</p>";
                    }
                }
                
                if ($table === 'products') {
                    $required = ['id', 'name', 'description', 'price', 'category_id', 'seller_id', 'carbon_saved', 'stock_quantity', 'image_url', 'is_approved', 'created_at'];
                    $missing = array_diff($required, $column_names);
                    if (!empty($missing)) {
                        echo "<p style='color: red;'>❌ Missing columns in products: " . implode(', ', $missing) . "</p>";
                    } else {
                        echo "<p style='color: green;'>✅ Products table has all required columns</p>";
                    }
                }
                
            } else {
                echo "<p style='color: red;'>No columns found for table $table</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error checking table $table: " . $e->getMessage() . "</p>";
        }
        echo "<hr>";
    }
    
    // Test a simple query on each table
    echo "<h2>3. Data Sample Test</h2>";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
            $count = $stmt->fetch()['count'];
            echo "<p>$table: $count records</p>";
            
            // Try to get first record to see actual data structure
            $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
            $sample = $stmt->fetch();
            if ($sample) {
                echo "<p><strong>Sample columns in $table:</strong> " . implode(', ', array_keys($sample)) . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error querying $table: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>