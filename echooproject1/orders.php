<?php
$page_title = 'Order History';
require_once 'includes/header.php';

requireLogin();

// Get user's orders with detailed information
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_items
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Get order details for each order
$order_details = [];
foreach ($orders as $order) {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_url, u.username as seller_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN users u ON p.seller_id = u.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $order_details[$order['id']] = $stmt->fetchAll();
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Order History</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Back to Dashboard
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
            <p class="text-gray-600 mb-4">Start shopping for sustainable products to see your order history here.</p>
            <a href="products.php" class="bg-eco-green text-white px-6 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Order Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Order #<?php echo $order['id']; ?></h3>
                            <p class="text-sm text-gray-600">
                                Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-eco-green"><?php echo formatCurrency($order['total_amount']); ?></p>
                            <div class="flex items-center space-x-2">
                                <span class="px-3 py-1 text-sm rounded-full <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <span class="text-sm text-green-600">🌱 <?php echo formatCarbon($order['total_carbon_saved']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Items (<?php echo $order['total_items']; ?>)</h4>
                    <div class="space-y-4">
                        <?php foreach ($order_details[$order['id']] as $item): ?>
                        <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="w-16 h-16 object-cover rounded-lg">
                            
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                <p class="text-sm text-gray-600">Sold by @<?php echo htmlspecialchars($item['seller_name']); ?></p>
                                <p class="text-sm text-green-600">🌱 Saved <?php echo formatCarbon($item['carbon_saved']); ?> per item</p>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">Qty: <?php echo $item['quantity']; ?></p>
                                <p class="text-eco-green font-bold"><?php echo formatCurrency($item['price']); ?> each</p>
                                <p class="text-sm text-gray-600">Total: <?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <p><strong>Environmental Impact:</strong> You saved <?php echo formatCarbon($order['total_carbon_saved']); ?> with this order!</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">Order Total: <?php echo formatCurrency($order['total_amount']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>