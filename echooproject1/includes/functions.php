<?php
// Common functions for the EcoStore application

// Start session if not already started
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Check user role
function hasRole($role) {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Redirect based on role
function redirectByRole() {
    startSession();
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: dashboard.php?view=admin');
            break;
        case 'seller':
            header('Location: dashboard.php?view=seller');
            break;
        case 'buyer':
        default:
            header('Location: dashboard.php?view=buyer');
            break;
    }
    exit();
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Format carbon saved
function formatCarbon($amount) {
    return number_format($amount, 1) . ' kg CO₂';
}

// Get cart count for current user
function getCartCount($pdo) {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $stmt = $pdo->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

// Add product to cart
function addToCart($pdo, $product_id, $quantity = 1) {
    if (!isLoggedIn()) {
        return false;
    }
    
    try {
        // Check if item already exists in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing item
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
            return $stmt->execute([$quantity, $existing['id']]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            return $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        }
    } catch (Exception $e) {
        error_log("Add to cart error: " . $e->getMessage());
        return false;
    }
}
    

// Get user badges
function getUserBadges($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT b.* FROM badges b 
        JOIN user_badges ub ON b.id = ub.badge_id 
        WHERE ub.user_id = ? 
        ORDER BY ub.earned_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Check and award badges
function checkAndAwardBadges($pdo, $user_id) {
    try {
        // Get user's total carbon saved
        $stmt = $pdo->prepare("SELECT total_carbon_saved FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) return;
        
        // Get badges user hasn't earned yet
        $stmt = $pdo->prepare("
            SELECT b.* FROM badges b 
            WHERE b.carbon_threshold <= ? 
            AND b.id NOT IN (
                SELECT badge_id FROM user_badges WHERE user_id = ?
            )
        ");
        $stmt->execute([$user['total_carbon_saved'], $user_id]);
        $newBadges = $stmt->fetchAll();
        
        // Award new badges
        foreach ($newBadges as $badge) {
            $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?) ON CONFLICT (user_id, badge_id) DO NOTHING");
            $stmt->execute([$user_id, $badge['id']]);
        }
    } catch (Exception $e) {
        error_log("Badge award error: " . $e->getMessage());
    }
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Generate CSRF token
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>