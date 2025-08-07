<?php
/**
 * Interactive Admin User Creation Script
 * Run this in your browser to create a new admin user with a custom password
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><title>Create Admin User</title></head><body>";
echo "<h1>🔧 Create New Admin User</h1>";
echo "<hr>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        echo "<p style='color: red;'>❌ All fields are required!</p>";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                echo "<p style='color: red;'>❌ Username or email already exists!</p>";
            } else {
                // Create the admin user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, role, full_name, total_carbon_saved) 
                    VALUES (?, ?, ?, 'admin', ?, 0.00)
                ");
                
                if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
                    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                    echo "<h2 style='color: #155724;'>✅ Admin User Created Successfully!</h2>";
                    echo "<p><strong>Username:</strong> $username</p>";
                    echo "<p><strong>Email:</strong> $email</p>";
                    echo "<p><strong>Password:</strong> $password</p>";
                    echo "<p><strong>Role:</strong> admin</p>";
                    echo "<p><a href='login.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
                    echo "</div>";
                } else {
                    echo "<p style='color: red;'>❌ Failed to create admin user!</p>";
                }
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
        }
    }
}

// Show current admin users
try {
    $stmt = $pdo->query("SELECT id, username, email, full_name, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
    
    echo "<h2>Current Admin Users:</h2>";
    if (!empty($admins)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Created</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td><strong>{$admin['username']}</strong></td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td>{$admin['full_name']}</td>";
            echo "<td>" . date('Y-m-d H:i', strtotime($admin['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No admin users found!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading admin users: " . $e->getMessage() . "</p>";
}
?>

<h2>Create New Admin User:</h2>
<form method="POST" style="max-width: 500px;">
    <div style="margin-bottom: 15px;">
        <label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username:</label>
        <input type="text" id="username" name="username" required 
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email:</label>
        <input type="email" id="email" name="email" required 
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="full_name" style="display: block; margin-bottom: 5px; font-weight: bold;">Full Name:</label>
        <input type="text" id="full_name" name="full_name" required 
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
        <input type="password" id="password" name="password" required 
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <small style="color: #666;">Minimum 6 characters</small>
    </div>
    
    <button type="submit" 
            style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
        Create Admin User
    </button>
</form>

</body></html>