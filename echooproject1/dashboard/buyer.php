<?php
// Buyer Dashboard Content

// Get buyer stats
$stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$order_count = $stmt->fetch()['order_count'];

$stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$total_spent = $stmt->fetch()['total_spent'] ?? 0;

// Get user badges
$user_badges = getUserBadges($pdo, $_SESSION['user_id']);

// Get recent orders
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count,
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll();

// Calculate progress to next badge
$next_badge = null;
$progress_percentage = 0;

$stmt = $pdo->prepare("
    SELECT * FROM badges 
    WHERE carbon_threshold > ? 
    ORDER BY carbon_threshold ASC 
    LIMIT 1
");
$stmt->execute([$current_user['total_carbon_saved']]);
$next_badge = $stmt->fetch();

if ($next_badge) {
    $progress_percentage = ($current_user['total_carbon_saved'] / $next_badge['carbon_threshold']) * 100;
    $progress_percentage = min(100, $progress_percentage);
}
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🛍️</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $order_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">💰</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Spent</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($total_spent); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🌱</div>
            <div>
                <p class="text-sm font-medium text-gray-600">CO₂ Saved</p>
                <p class="text-2xl font-bold text-eco-green"><?php echo formatCarbon($current_user['total_carbon_saved']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🏆</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Badges Earned</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($user_badges); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Badges & Progress -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Your Eco Badges</h2>
        
        <?php if (!empty($user_badges)): ?>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <?php foreach ($user_badges as $badge): ?>
                <div class="text-center p-4 bg-eco-light rounded-lg">
                    <div class="text-3xl mb-2"><?php echo $badge['icon']; ?></div>
                    <h3 class="font-semibold text-eco-dark text-sm"><?php echo htmlspecialchars($badge['name']); ?></h3>
                    <p class="text-xs text-eco-dark"><?php echo formatCarbon($badge['carbon_threshold']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($next_badge): ?>
            <div class="border-t pt-4">
                <h3 class="font-semibold text-gray-900 mb-2">Next Badge: <?php echo htmlspecialchars($next_badge['name']); ?></h3>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                    <div class="bg-eco-green h-2 rounded-full" style="width: <?php echo $progress_percentage; ?>%"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <?php echo formatCarbon($current_user['total_carbon_saved']); ?> / <?php echo formatCarbon($next_badge['carbon_threshold']); ?>
                    (<?php echo number_format($progress_percentage, 1); ?>%)
                </p>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <div class="text-4xl mb-2">🌟</div>
                <p class="text-eco-green font-semibold">You've earned all available badges!</p>
                <p class="text-sm text-gray-600">You're a true eco champion!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Purchase History</h2>
            <a href="orders.php" class="text-eco-green hover:text-eco-dark text-sm">View All</a>
        </div>
        
        <?php if (!empty($recent_orders)): ?>
            <div class="space-y-4">
                <?php foreach ($recent_orders as $order): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-gray-900">Order #<?php echo $order['id']; ?></p>
                            <p class="text-sm text-gray-600"><?php echo $order['item_count']; ?> item(s)</p>
                            <?php if (!empty($order['product_names'])): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($order['product_names']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-eco-green"><?php echo formatCurrency($order['total_amount']); ?></p>
                            <p class="text-sm text-green-600">🌱 <?php echo formatCarbon($order['total_carbon_saved']); ?></p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                        <span class="px-2 py-1 text-xs rounded-full <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-4xl mb-2">📦</div>
                <p class="text-gray-600 mb-4">No orders yet</p>
                <a href="products.php" class="bg-eco-green text-white px-4 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                    Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="products.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-eco-green hover:bg-eco-light transition-colors">
            <div class="text-2xl mr-3">🛍️</div>
            <div>
                <p class="font-semibold text-gray-900">Browse Products</p>
                <p class="text-sm text-gray-600">Find sustainable items</p>
            </div>
        </a>
        
        <a href="cart.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-eco-green hover:bg-eco-light transition-colors">
            <div class="text-2xl mr-3">🛒</div>
            <div>
                <p class="font-semibold text-gray-900">View Cart</p>
                <p class="text-sm text-gray-600">Complete your purchase</p>
            </div>
        </a>
        
        <a href="leaderboard.php" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-eco-green hover:bg-eco-light transition-colors">
            <div class="text-2xl mr-3">🏆</div>
            <div>
                <p class="font-semibold text-gray-900">Leaderboard</p>
                <p class="text-sm text-gray-600">See your ranking</p>
            </div>
        </a>
    </div>
</div>