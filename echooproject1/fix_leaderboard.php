<?php
/**
 * Fix Leaderboard Data Script
 * Run this to add some test data for the leaderboard
 */

require_once 'config/database.php';

echo "<h1>Fix Leaderboard Data</h1>";
echo "<hr>";

try {
    // Check current user data
    echo "<h2>Current User Data:</h2>";
    $stmt = $pdo->query("SELECT username, role, total_carbon_saved FROM users ORDER BY total_carbon_saved DESC");
    $users = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Username</th><th>Role</th><th>CO₂ Saved</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['total_carbon_saved']} kg</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Update existing users with some carbon savings if they have 0
    echo "<h2>Updating User Carbon Savings:</h2>";
    
    $updates = [
        'ecobuyer' => 45.75,
        'greenseller' => 28.50
    ];
    
    foreach ($updates as $username => $carbon) {
        $stmt = $pdo->prepare("UPDATE users SET total_carbon_saved = ? WHERE username = ?");
        if ($stmt->execute([$carbon, $username])) {
            echo "<p style='color: green;'>✅ Updated $username to $carbon kg CO₂ saved</p>";
        }
    }
    
    // Add some additional test users for the leaderboard
    echo "<h2>Adding Test Users:</h2>";
    
    $test_users = [
        ['username' => 'ecowarrior1', 'email' => 'warrior1@test.com', 'full_name' => 'Eco Warrior One', 'role' => 'buyer', 'carbon' => 67.25],
        ['username' => 'greenliving', 'email' => 'green@test.com', 'full_name' => 'Green Living', 'role' => 'buyer', 'carbon' => 89.50],
        ['username' => 'sustainableshopper', 'email' => 'sustainable@test.com', 'full_name' => 'Sustainable Shopper', 'role' => 'buyer', 'carbon' => 123.75],
        ['username' => 'planetprotector', 'email' => 'planet@test.com', 'full_name' => 'Planet Protector', 'role' => 'buyer', 'carbon' => 156.00],
        ['username' => 'climatechampion', 'email' => 'climate@test.com', 'full_name' => 'Climate Champion', 'role' => 'buyer', 'carbon' => 234.50]
    ];
    
    foreach ($test_users as $user) {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$user['username'], $user['email']]);
        
        if (!$stmt->fetch()) {
            // Create user with hashed password (password is 'test123')
            $hashed_password = password_hash('test123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $user['username'], 
                $user['email'], 
                $hashed_password, 
                $user['role'], 
                $user['full_name'], 
                $user['carbon']
            ])) {
                echo "<p style='color: green;'>✅ Added {$user['username']} with {$user['carbon']} kg CO₂ saved</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ User {$user['username']} already exists</p>";
        }
    }
    
    // Test leaderboard query
    echo "<h2>Testing Leaderboard Query:</h2>";
    
    $stmt = $pdo->query("
        SELECT u.username, u.full_name, u.total_carbon_saved, u.role,
               ROW_NUMBER() OVER (ORDER BY u.total_carbon_saved DESC) as rank
        FROM users u 
        WHERE u.role != 'admin' AND u.total_carbon_saved > 0
        ORDER BY u.total_carbon_saved DESC 
        LIMIT 10
    ");
    $leaderboard = $stmt->fetchAll();
    
    if (!empty($leaderboard)) {
        echo "<p style='color: green;'>✅ Leaderboard is now working!</p>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Rank</th><th>Username</th><th>Full Name</th><th>Role</th><th>CO₂ Saved</th></tr>";
        foreach ($leaderboard as $user) {
            echo "<tr>";
            echo "<td>{$user['rank']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['total_carbon_saved']} kg</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Leaderboard Fix Complete!</h2>";
    echo "<p>Your leaderboard should now be working. Visit <a href='leaderboard.php'>leaderboard.php</a> to see it in action.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>