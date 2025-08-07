<?php
/**
 * Check Database Structure
 * This will show us what tables and columns actually exist
 */

require_once 'config/database.php';

echo "<h1>Database Structure Check</h1>";
echo "<hr>";

try {
    // Check connection
    echo "<h2>1. Connection Test</h2>";
    $stmt = $pdo->query("SELECT current_database(), version()");
    $info = $stmt->fetch();
    echo "<p style='color: green;'>✅ Connected to database: " . $info['current_database'] . "</p>";
    echo "<p>PostgreSQL Version: " . substr($info['version'], 0, 50) . "...</p>";
    
    // List all tables
    echo "<h2>2. Existing Tables</h2>";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: red;'>❌ No tables found in the database!</p>";
        echo "<p>The database schema needs to be created.</p>";
    } else {
        echo "<p>Found " . count($tables) . " tables:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check structure of each table
        echo "<h2>3. Table Structures</h2>";
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
                    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                    echo "<tr><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
                    foreach ($columns as $col) {
                        echo "<tr>";
                        echo "<td>{$col['column_name']}</td>";
                        echo "<td>{$col['data_type']}</td>";
                        echo "<td>{$col['is_nullable']}</td>";
                        echo "<td>" . ($col['column_default'] ?: 'NULL') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No columns found for table $table</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error checking table $table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>