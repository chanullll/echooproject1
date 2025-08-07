<?php
/**
 * PostgreSQL Status Checker
 * This script helps diagnose PostgreSQL connection issues
 */

echo "<!DOCTYPE html><html><head><title>PostgreSQL Status Check</title></head><body>";
echo "<h1>🔍 PostgreSQL Connection Diagnostics</h1>";
echo "<hr>";

// Check if PDO PostgreSQL extension is loaded
echo "<h2>1. PHP PDO PostgreSQL Extension Check</h2>";
if (extension_loaded('pdo_pgsql')) {
    echo "<p style='color: green;'>✅ PDO PostgreSQL extension is loaded</p>";
} else {
    echo "<p style='color: red;'>❌ PDO PostgreSQL extension is NOT loaded</p>";
    echo "<p><strong>Solution:</strong> Install php-pgsql extension</p>";
    echo "<ul>";
    echo "<li><strong>Windows (XAMPP):</strong> Uncomment <code>extension=pdo_pgsql</code> in php.ini</li>";
    echo "<li><strong>Ubuntu/Debian:</strong> <code>sudo apt-get install php-pgsql</code></li>";
    echo "<li><strong>CentOS/RHEL:</strong> <code>sudo yum install php-pgsql</code></li>";
    echo "</ul>";
}

// Test different connection scenarios
echo "<h2>2. Connection Tests</h2>";

$test_configs = [
    ['host' => 'localhost', 'port' => '5432', 'user' => 'postgres', 'pass' => '1234'],
    ['host' => 'localhost', 'port' => '5432', 'user' => 'postgres', 'pass' => 'postgres'],
    ['host' => 'localhost', 'port' => '5432', 'user' => 'postgres', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => '5432', 'user' => 'postgres', 'pass' => '1234'],
    ['host' => 'localhost', 'port' => '5433', 'user' => 'postgres', 'pass' => '1234'],
];

foreach ($test_configs as $i => $config) {
    echo "<h3>Test " . ($i + 1) . ": {$config['host']}:{$config['port']} (user: {$config['user']})</h3>";
    
    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname=postgres";
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetch();
        
        echo "<p style='color: green;'>✅ Connection successful!</p>";
        echo "<p><strong>PostgreSQL Version:</strong> " . substr($version['version'], 0, 50) . "...</p>";
        
        // Check if echostore database exists
        $stmt = $pdo->query("SELECT 1 FROM pg_database WHERE datname = 'echostore'");
        if ($stmt->fetch()) {
            echo "<p style='color: green;'>✅ Database 'echostore' exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Database 'echostore' does not exist</p>";
            echo "<p><strong>Solution:</strong> Create database with: <code>CREATE DATABASE echostore;</code></p>";
        }
        
        echo "<div style='background-color: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Working Configuration Found!</h4>";
        echo "<p>Update your <code>config/database.php</code> with these settings:</p>";
        echo "<pre>";
        echo "\$host = '{$config['host']}';\n";
        echo "\$port = '{$config['port']}';\n";
        echo "\$username = '{$config['user']}';\n";
        echo "\$password = '{$config['pass']}';\n";
        echo "</pre>";
        echo "</div>";
        
        break; // Stop testing once we find a working connection
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    }
}

// Check if PostgreSQL is running (attempt to connect to port)
echo "<h2>3. Port Connectivity Check</h2>";
$ports_to_check = [5432, 5433, 5434];

foreach ($ports_to_check as $port) {
    echo "<h4>Checking port $port...</h4>";
    
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 5);
    if ($connection) {
        echo "<p style='color: green;'>✅ Port $port is open and accepting connections</p>";
        fclose($connection);
    } else {
        echo "<p style='color: red;'>❌ Port $port is not accessible (Error: $errno - $errstr)</p>";
    }
}

// System-specific instructions
echo "<h2>4. System-Specific Instructions</h2>";

$os = PHP_OS_FAMILY;
echo "<p><strong>Detected OS:</strong> $os</p>";

switch ($os) {
    case 'Windows':
        echo "<h3>Windows Instructions:</h3>";
        echo "<ol>";
        echo "<li><strong>Check if PostgreSQL is installed:</strong>";
        echo "<ul><li>Look for PostgreSQL in Start Menu</li>";
        echo "<li>Check <code>C:\\Program Files\\PostgreSQL\\</code></li></ul></li>";
        echo "<li><strong>Start PostgreSQL Service:</strong>";
        echo "<ul><li>Press Win+R, type <code>services.msc</code></li>";
        echo "<li>Find 'postgresql-x64-XX' service</li>";
        echo "<li>Right-click → Start</li></ul></li>";
        echo "<li><strong>Install if not present:</strong>";
        echo "<ul><li>Download from <a href='https://www.postgresql.org/download/windows/'>postgresql.org</a></li></ul></li>";
        echo "</ol>";
        break;
        
    case 'Darwin':
        echo "<h3>macOS Instructions:</h3>";
        echo "<ol>";
        echo "<li><strong>Check if running:</strong> <code>brew services list | grep postgresql</code></li>";
        echo "<li><strong>Start PostgreSQL:</strong> <code>brew services start postgresql</code></li>";
        echo "<li><strong>Install if needed:</strong> <code>brew install postgresql</code></li>";
        echo "</ol>";
        break;
        
    case 'Linux':
        echo "<h3>Linux Instructions:</h3>";
        echo "<ol>";
        echo "<li><strong>Check status:</strong> <code>sudo systemctl status postgresql</code></li>";
        echo "<li><strong>Start service:</strong> <code>sudo systemctl start postgresql</code></li>";
        echo "<li><strong>Install if needed:</strong> <code>sudo apt-get install postgresql postgresql-contrib</code></li>";
        echo "</ol>";
        break;
}

echo "<h2>5. Next Steps</h2>";
echo "<ol>";
echo "<li>Ensure PostgreSQL is installed and running</li>";
echo "<li>Update <code>config/database.php</code> with working credentials</li>";
echo "<li>Create the 'echostore' database if it doesn't exist</li>";
echo "<li>Run the database setup scripts</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Need more help?</strong> Check the <a href='database_troubleshooting.md'>detailed troubleshooting guide</a></p>";

echo "</body></html>";
?>