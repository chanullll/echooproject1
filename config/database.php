@@ .. @@
 <?php
 // Database configuration
 $host = getenv('DB_HOST') ?: 'localhost';
-$dbname = getenv('DB_NAME') ?: 'echostore';  // Fixed database name
+$dbname = getenv('DB_NAME') ?: 'echostore';
 $username = getenv('DB_USER') ?: 'postgres';
-$password = getenv('DB_PASSWORD') ?: '1234';  // Updated with your password
+$password = getenv('DB_PASSWORD') ?: 'your_password_here';  // UPDATE THIS WITH YOUR ACTUAL PASSWORD
 $port = getenv('DB_PORT') ?: '5432';
 
 try {
@@ .. @@
     // Test the connection
     $pdo->query("SELECT 1");
 } catch(PDOException $e) {
     // Log error and show user-friendly message
     error_log("Database connection failed: " . $e->getMessage());
     
-    // For development, show detailed error
-    if (getenv('APP_ENV') === 'development') {
-        die("Database connection failed: " . $e->getMessage());
-    } else {
-        die("Database connection failed. Please try again later.");
-    }
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