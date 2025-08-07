<?php
/**
 * Verify Demo Accounts Script
 * This will check if demo accounts exist and have correct passwords
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><title>Demo Account Verification</title></head><body>";
echo "<h1>🔍 Demo Account Verification</h1>";
echo "<hr>";

try {
    echo "<h2>Checking Demo Accounts...</h2>";
    
    // Check all users in the system
    $stmt = $pdo->query("
        SELECT id, username, email, role, full_name, total_carbon_saved, created_at 
        FROM users 
        ORDER BY role, username
    ");
    $all_users = $stmt->fetchAll();
    
    echo "<h3>All Users in Database:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Full Name</th><th>Carbon Saved</th><th>Created</th>";
    echo "</tr>";
    
    foreach ($all_users as $user) {
        $row_color = '';
        if ($user['role'] === 'admin') $row_color = 'background-color: #ffe6e6;';
        elseif ($user['role'] === 'seller') $row_color = 'background-color: #e6f3ff;';
        elseif ($user['role'] === 'buyer') $row_color = 'background-color: #e6ffe6;';
        
        echo "<tr style='$row_color'>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['email']}</td>";
        echo "<td><span style='padding: 2px 6px; border-radius: 3px; font-size: 12px; " . 
             ($user['role'] === 'admin' ? 'background-color: #ff9999; color: white;' : 
              ($user['role'] === 'seller' ? 'background-color: #66b3ff; color: white;' : 
               'background-color: #66ff66; color: black;')) . "'>" . 
             ucfirst($user['role']) . "</span></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['total_carbon_saved']} kg</td>";
        echo "<td>" . date('Y-m-d H:i', strtotime($user['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Testing Demo Account Passwords:</h3>";
    
    // Test demo accounts
    $demo_tests = [
        ['username' => 'admin', 'password' => 'admin123', 'expected_role' => 'admin'],
        ['username' => 'greenseller', 'password' => 'seller123', 'expected_role' => 'seller'],
        ['username' => 'ecobuyer', 'password' => 'buyer123', 'expected_role' => 'buyer']
    ];
    
    $all_working = true;
    
    foreach ($demo_tests as $test) {
        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h4>Testing: {$test['username']} / {$test['password']}</h4>";
        
        // Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$test['username']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "<p style='color: red;'>❌ User '{$test['username']}' not found in database</p>";
            $all_working = false;
        } else {
            echo "<p style='color: green;'>✅ User found - ID: {$user['id']}, Role: {$user['role']}</p>";
            
            // Test password
            if (password_verify($test['password'], $user['password'])) {
                echo "<p style='color: green;'>✅ Password verification successful</p>";
                
                // Check role
                if ($user['role'] === $test['expected_role']) {
                    echo "<p style='color: green;'>✅ Role matches expected: {$test['expected_role']}</p>";
                } else {
                    echo "<p style='color: red;'>❌ Role mismatch - Expected: {$test['expected_role']}, Got: {$user['role']}</p>";
                    $all_working = false;
                }
            } else {
                echo "<p style='color: red;'>❌ Password verification failed</p>";
                echo "<p style='color: orange;'>⚠️ Password hash in DB: " . substr($user['password'], 0, 20) . "...</p>";
                $all_working = false;
            }
        }
        echo "</div>";
    }
    
    if ($all_working) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2 style='color: #155724;'>🎉 All Demo Accounts Working!</h2>";
        echo "<p>You can now login with these credentials:</p>";
        echo "<ul>";
        echo "<li><strong>Admin Access:</strong> Username: <code>admin</code>, Password: <code>admin123</code></li>";
        echo "<li><strong>Seller Access:</strong> Username: <code>greenseller</code>, Password: <code>seller123</code></li>";
        echo "<li><strong>Buyer Access:</strong> Username: <code>ecobuyer</code>, Password: <code>buyer123</code></li>";
        echo "</ul>";
        echo "<p><a href='login.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h2 style='color: #721c24;'>❌ Demo Accounts Need Fixing</h2>";
        echo "<p>Some demo accounts are not working properly. Let's fix them...</p>";
        echo "</div>";
        
        // Fix the accounts
        echo "<h3>Fixing Demo Accounts...</h3>";
        
        // Delete existing demo accounts and recreate them
        $demo_emails = ['admin@ecostore.com', 'seller@ecostore.com', 'buyer@ecostore.com'];
        foreach ($demo_emails as $email) {
            try {
                $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
                echo "<p>Removed existing account with email: $email</p>";
            } catch (Exception $e) {
                // Ignore errors
            }
        }
        
        // Create fresh demo accounts
        $demo_accounts = [
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
        
        foreach ($demo_accounts as $account) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([
                    $account['username'],
                    $account['email'],
                    $account['password'],
                    $account['role'],
                    $account['full_name'],
                    $account['total_carbon_saved']
                ])) {
                    echo "<p style='color: green;'>✅ Created demo account: {$account['username']}</p>";
                } else {
                    echo "<p style='color: red;'>❌ Failed to create: {$account['username']}</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error creating {$account['username']}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #155724;'>✅ Demo Accounts Fixed!</h3>";
        echo "<p>Try logging in again with:</p>";
        echo "<ul>";
        echo "<li><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></li>";
        echo "<li><strong>Seller:</strong> username: <code>greenseller</code>, password: <code>seller123</code></li>";
        echo "<li><strong>Buyer:</strong> username: <code>ecobuyer</code>, password: <code>buyer123</code></li>";
        echo "</ul>";
        echo "<p><a href='login.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
        echo "</div>";
    }
    
    echo "<h3>Login Process Explanation:</h3>";
    echo "<div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>How Login Works:</strong></p>";
    echo "<ol>";
    echo "<li>You enter your <strong>username</strong> (or email) and <strong>password</strong> on the login page</li>";
    echo "<li>The system looks up your account in the database</li>";
    echo "<li>If found and password matches, you're logged in with your stored role</li>";
    echo "<li>You're automatically redirected to the appropriate dashboard:</li>";
    echo "<ul>";
    echo "<li><strong>Admin</strong> → Admin Dashboard (can view all dashboards)</li>";
    echo "<li><strong>Seller</strong> → Seller Dashboard (manage products, view sales)</li>";
    echo "<li><strong>Buyer</strong> → Buyer Dashboard (view orders, badges, shopping)</li>";
    echo "</ul>";
    echo "</ol>";
    echo "<p><strong>Note:</strong> You don't select a role during login - your role was set when your account was created!</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}

echo "</body></html>";
?>