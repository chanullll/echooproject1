<?php
$host = 'localhost';
$port = '5432';
$dbname = 'echostore';
$user = 'postgres';
$password = '1234'; 

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    // Set error mode to Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional debug log
    // echo "✅ Connected to PostgreSQL database successfully!";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
