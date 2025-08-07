@@ .. @@
 <?php
 // Database configuration
-$dbname = getenv('DB_NAME') ?: 'echostore';  // Fixed database name
 $username = getenv('DB_USER') ?: 'postgres';
$host = 'localhost';
$dbname = 'echostore';
$username = 'postgres';
$password = '1234';  // Update this with your actual PostgreSQL password
$port = '5432';
+$password = getenv('DB_PASSWORD') ?: 'your_password_here';  // UPDATE THIS WITH YOUR ACTUAL PASSWORD
 $port = getenv('DB_PORT') ?: '5432';
 
 try {
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
 } catch(PDOException $e) {
     error_log("Database connection failed: " . $e->getMessage());
-    // For development, show detailed error
-    if (getenv('APP_ENV') === 'development') {
-        die("Database connection failed: " . $e->getMessage());
-    } else {
-        die("Database connection failed. Please try again later.");
-    }
    die("Database connection failed: " . $e->getMessage() . 
        "\n\nPlease ensure:\n" .
        "1. PostgreSQL is running\n" .
        "2. Database 'echostore' exists\n" .
        "3. Username and password are correct\n" .
        "4. Host and port are accessible");
+    // Show detailed error for troubleshooting
+    die("
+    <h1>Database Connection Failed</h1>
+    <p><strong>Error:</strong> " . $e->getMessage() . "</p>
+    <h2>Troubleshooting Steps:</h2>
+    <ol>
+        <li><strong>Check if PostgreSQL is running:</strong>
+            <ul>
+                <li>Windows: Check Services → postgresql service</li>
+                <li>Mac: <code>brew services list | grep postgresql</code></li>
+                <li>Linux: <code>sudo systemctl status postgresql</code></li>
+            </ul>
+        </li>
+        <li><strong>Install PostgreSQL if not installed:</strong>
+            <ul>
+                <li>Download from: <a href='https://www.postgresql.org/download/'>postgresql.org</a></li>
+            </ul>
+        </li>
+        <li><strong>Update password in config/database.php:</strong>
+            <ul>
+                <li>Change 'your_password_here' to your actual PostgreSQL password</li>
+            </ul>
+        </li>
+        <li><strong>Create database:</strong>
+            <ul>
+                <li>Run: <code>CREATE DATABASE echostore;</code> in PostgreSQL</li>
+            </ul>
+        </li>
+    </ol>
+    <p><a href='database_troubleshooting.md'>View detailed troubleshooting guide</a></p>
+    ");
 }
 ?>