<?php
/**
 * Update Demo Users with Correct Data
 * This will ensure the demo users have the correct usernames and passwords
 */

require_once 'config/database.php';

echo "<h1>Updating Demo Users</h1>";
echo "<hr>";

try {
    echo "<h2>Updating demo user accounts...</h2>";
    
    // Check current users
    $stmt = $pdo->query("SELECT id, name, email, username, role FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h3>Current users in database:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . ($user['name'] ?? 'NULL') . "</td>";
        echo "<td>" . ($user['username'] ?? 'NULL') . "</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Update or create demo users with correct passwords
    $demo_users = [
        [
            'username' => 'admin',
            'email' => 'admin@ecostore.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'full_name' => 'Admin User',
            'total_carbon_saved' => 0.00
        ],
        [
            'username' => 'greenseller',
            'email' => 'seller@ecostore.com', 
            'password' => password_hash('seller123', PASSWORD_DEFAULT),
            'role' => 'seller',
            'full_name' => 'Green Seller',
            'total_carbon_saved' => 15.50
        ],
        [
            'username' => 'ecobuyer',
            'email' => 'buyer@ecostore.com',
            'password' => password_hash('buyer123', PASSWORD_DEFAULT),
            'role' => 'buyer', 
            'full_name' => 'Eco Buyer',
            'total_carbon_saved' => 32.75
        ]
    ];
    
    echo "<h3>Updating demo users...</h3>";
    
    foreach ($demo_users as $user) {
        // Check if user exists by email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing user
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    username = ?, 
                    password = ?, 
                    role = ?, 
                    full_name = ?, 
                    total_carbon_saved = ?,
                    name = ?
                WHERE email = ?
            ");
            
            if ($stmt->execute([
                $user['username'],
                $user['password'], 
                $user['role'],
                $user['full_name'],
                $user['total_carbon_saved'],
                $user['full_name'], // Also update name field
                $user['email']
            ])) {
                echo "<p style='color: green;'>✅ Updated user: {$user['username']}</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update user: {$user['username']}</p>";
            }
        } else {
            // Create new user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role, full_name, total_carbon_saved, name) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $user['username'],
                $user['email'],
                $user['password'],
                $user['role'], 
                $user['full_name'],
                $user['total_carbon_saved'],
                $user['full_name'] // Also set name field
            ])) {
                echo "<p style='color: green;'>✅ Created user: {$user['username']}</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to create user: {$user['username']}</p>";
            }
        }
    }
    
    // Verify the updates
    echo "<h3>Verification - Updated users:</h3>";
    $stmt = $pdo->query("SELECT id, name, username, email, role, total_carbon_saved FROM users ORDER BY id");
    $updated_users = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Carbon Saved</th></tr>";
    foreach ($updated_users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . ($user['name'] ?? 'NULL') . "</td>";
        echo "<td>" . ($user['username'] ?? 'NULL') . "</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['total_carbon_saved']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>✅ Demo Users Updated Successfully!</h2>";
    echo "<p>You can now login with these accounts:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: 'admin', password: 'admin123'</li>";
    echo "<li><strong>Seller:</strong> username: 'greenseller', password: 'seller123'</li>";
    echo "<li><strong>Buyer:</strong> username: 'ecobuyer', password: 'buyer123'</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error updating demo users: " . $e->getMessage() . "</p>";
}
?>