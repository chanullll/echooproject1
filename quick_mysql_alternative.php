<?php
/**
 * Quick MySQL Alternative Setup
 * If you prefer to use MySQL instead of PostgreSQL
 */

echo "<!DOCTYPE html><html><head><title>MySQL Alternative Setup</title></head><body>";
echo "<h1>🔄 Switch to MySQL Alternative</h1>";
echo "<hr>";

echo "<p>If you're having trouble with PostgreSQL and prefer to use MySQL (which comes with XAMPP), here's how to switch:</p>";

echo "<h2>Option 1: Use MySQL Instead</h2>";
echo "<p>If you have XAMPP/WAMP with MySQL running:</p>";

echo "<h3>1. Update Database Configuration</h3>";
echo "<p>Replace your <code>config/database.php</code> with this MySQL version:</p>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars('<?php
// MySQL Database configuration for XAMPP
$host = "localhost";
$dbname = "ecostore";
$username = "root";
$password = "";  // Usually empty for XAMPP
$port = "3306";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>');
echo "</pre>";

echo "<h3>2. Create MySQL Database</h3>";
echo "<p>In phpMyAdmin or MySQL command line:</p>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo "CREATE DATABASE ecostore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
echo "</pre>";

echo "<h3>3. Update SQL Scripts for MySQL</h3>";
echo "<p>The main differences for MySQL:</p>";
echo "<ul>";
echo "<li>Change <code>SERIAL</code> to <code>AUTO_INCREMENT</code></li>";
echo "<li>Change <code>DECIMAL(10,2)</code> stays the same</li>";
echo "<li>Change <code>TIMESTAMP DEFAULT CURRENT_TIMESTAMP</code> stays the same</li>";
echo "<li>Change <code>VARCHAR(50)</code> stays the same</li>";
echo "</ul>";

// Test MySQL connection
echo "<h2>Test MySQL Connection</h2>";

try {
    // Try to connect to MySQL
    $mysql_pdo = new PDO("mysql:host=localhost;port=3306", "root", "", [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p style='color: green;'>✅ MySQL connection successful!</p>";
    
    // Check if database exists
    $stmt = $mysql_pdo->query("SHOW DATABASES LIKE 'ecostore'");
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✅ Database 'ecostore' exists</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Database 'ecostore' does not exist</p>";
        echo "<p>Creating database...</p>";
        
        try {
            $mysql_pdo->exec("CREATE DATABASE ecostore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p style='color: green;'>✅ Database 'ecostore' created successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Failed to create database: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ MySQL is Ready!</h3>";
    echo "<p>You can now:</p>";
    echo "<ol>";
    echo "<li>Update your <code>config/database.php</code> with the MySQL configuration above</li>";
    echo "<li>Run the MySQL version of the setup scripts</li>";
    echo "<li>Start using your EcoStore application</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Make sure:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP/WAMP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>Port 3306 is not blocked</li>";
    echo "</ul>";
}

echo "<h2>Option 2: Install PostgreSQL Properly</h2>";
echo "<p>If you prefer to stick with PostgreSQL:</p>";
echo "<ol>";
echo "<li><strong>Download PostgreSQL:</strong> <a href='https://www.postgresql.org/download/'>postgresql.org</a></li>";
echo "<li><strong>Install with default settings</strong></li>";
echo "<li><strong>Remember the password you set for 'postgres' user</strong></li>";
echo "<li><strong>Update config/database.php with your password</strong></li>";
echo "</ol>";

echo "<h2>Recommendation</h2>";
echo "<div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>For beginners:</strong> If you already have XAMPP running, switching to MySQL is easier.</p>";
echo "<p><strong>For production:</strong> PostgreSQL is more robust and feature-rich.</p>";
echo "</div>";

echo "</body></html>";
?>