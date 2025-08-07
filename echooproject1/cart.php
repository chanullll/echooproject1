<?php
$page_title = 'Shopping Cart';
require_once 'includes/header.php';

requireLogin();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        // Update quantities
        foreach ($_POST['quantities'] as $cart_id => $quantity) {
            $quantity = max(0, (int)$quantity);
            if ($quantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
            } else {
                // Remove item if quantity is 0
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $_SESSION['user_id']]);
            }
        }
        $success_message = "Cart updated successfully!";
    } elseif (isset($_POST['remove_item'])) {
        // Remove specific item
        $cart_id = (int)$_POST['cart_id'];
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
        $success_message = "Item removed from cart!";
    } elseif (isset($_POST['checkout'])) {
        // Process checkout (simulation)
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.price, p.carbon_saved 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll();
        
        if (!empty($cart_items)) {
            $total_amount = 0;
            $total_carbon = 0;
            
            // Calculate totals
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
                $total_carbon += $item['carbon_saved'] * $item['quantity'];
            }
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, total_carbon_saved, status) 
                VALUES (?, ?, ?, 'completed')
            ");
            $stmt->execute([$_SESSION['user_id'], $total_amount, $total_carbon]);
            $order_id = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cart_items as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price, carbon_saved) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order_id, 
                    $item['product_id'], 
                    $item['quantity'], 
                    $item['price'], 
                    $item['carbon_saved']
                ]);
                
                // Update product stock quantity
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE id = ? AND stock_quantity >= ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            }
            
            // Update user's total carbon saved
            $stmt = $pdo->prepare("
                UPDATE users 
                SET total_carbon_saved = total_carbon_saved + ? 
                WHERE id = ?
            ");
            $stmt->execute([$total_carbon, $_SESSION['user_id']]);
            
            // Check and award badges
            checkAndAwardBadges($pdo, $_SESSION['user_id']);
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $checkout_success = true;
            $checkout_total = $total_amount;
            $checkout_carbon = $total_carbon;
        }
    }
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.carbon_saved, p.image_url, p.stock_quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calculate totals
$total_amount = 0;
$total_carbon = 0;
$total_items = 0;

foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
    $total_carbon += $item['carbon_saved'] * $item['quantity'];
    $total_items += $item['quantity'];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

    <!-- Success Messages -->
    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Checkout Success -->
    <?php if (isset($checkout_success)): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
            <div class="text-center">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-2xl font-bold text-green-800 mb-2">Order Completed Successfully!</h2>
                <p class="text-green-700 mb-4">
                    Thank you for your eco-friendly purchase! You've saved <strong><?php echo formatCarbon($checkout_carbon); ?></strong> 
                    and spent <strong><?php echo formatCurrency($checkout_total); ?></strong>.
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="dashboard.php" class="bg-eco-green text-white px-6 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                        View Orders
                    </a>
                    <a href="products.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🛒</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
            <p class="text-gray-600 mb-6">Start shopping for sustainable products to make a positive impact!</p>
            <a href="products.php" class="bg-eco-green text-white px-6 py-3 rounded-lg hover:bg-eco-dark transition-colors">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <form method="POST">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Cart Items (<?php echo $total_items; ?>)</h2>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="p-6 flex items-center space-x-4">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-20 h-20 object-cover rounded-lg">
                                
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <a href="product.php?id=<?php echo $item['product_id']; ?>" class="hover:text-eco-green transition-colors">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-600">🌱 Saves <?php echo formatCarbon($item['carbon_saved']); ?> per item</p>
                                    <p class="text-lg font-bold text-eco-green"><?php echo formatCurrency($item['price']); ?></p>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label for="qty_<?php echo $item['id']; ?>" class="text-sm text-gray-600">Qty:</label>
                                    <select name="quantities[<?php echo $item['id']; ?>]" 
                                            id="qty_<?php echo $item['id']; ?>"
                                            class="border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green">
                                        <?php for ($i = 0; $i <= min(10, $item['stock_quantity']); $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i === $item['quantity'] ? 'selected' : ''; ?>>
                                                <?php echo $i === 0 ? 'Remove' : $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">
                                        <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                                    </p>
                                    <p class="text-sm text-green-600">
                                        🌱 <?php echo formatCarbon($item['carbon_saved'] * $item['quantity']); ?>
                                    </p>
                                </div>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_item" 
                                            class="text-red-500 hover:text-red-700 transition-colors"
                                            onclick="return confirm('Remove this item from cart?')">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="px-6 py-4 border-t border-gray-200">
                            <button type="submit" name="update_cart" 
                                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                                Update Cart
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Items (<?php echo $total_items; ?>)</span>
                            <span class="font-semibold"><?php echo formatCurrency($total_amount); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-semibold text-green-600">FREE</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-eco-green"><?php echo formatCurrency($total_amount); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Environmental Impact -->
                    <div class="bg-eco-light p-4 rounded-lg mb-6">
                        <h3 class="font-semibold text-eco-dark mb-2">🌍 Your Impact</h3>
                        <p class="text-eco-dark text-sm">
                            This order will save <strong><?php echo formatCarbon($total_carbon); ?></strong> 
                            compared to conventional products!
                        </p>
                    </div>
                    
                    <form method="POST">
                        <button type="button" onclick="openPaymentModal(<?php echo $total_amount; ?>, <?php echo htmlspecialchars(json_encode(array_map(function($item) { return ['name' => $item['name'], 'quantity' => $item['quantity'], 'price' => $item['price']]; }, $cart_items))); ?>)" 
                                class="w-full bg-eco-green text-white py-3 rounded-lg font-semibold hover:bg-eco-dark smooth-transition btn-ripple hover-glow">
                            💳 Proceed to Payment
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="products.php" class="text-eco-green hover:text-eco-dark smooth-transition text-sm">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full animate-scale-in">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">💳 Payment Gateway</h2>
                    <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 smooth-transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Order Summary -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Order Summary</h3>
                    <div id="paymentItems" class="space-y-2 mb-4"></div>
                    <div class="border-t pt-2">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-lg">Total:</span>
                            <span id="paymentTotal" class="font-bold text-lg text-eco-green"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <form id="paymentForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green"
                               oninput="formatCardNumber(this)">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                            <input type="text" placeholder="MM/YY" maxlength="5"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green"
                                   oninput="formatExpiryDate(this)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                            <input type="text" placeholder="123" maxlength="3"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                        <input type="text" placeholder="John Doe"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green">
                    </div>
                    
                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="processPayment()" 
                                class="flex-1 bg-eco-green text-white py-3 px-4 rounded-md hover:bg-eco-dark smooth-transition btn-ripple hover-glow">
                            💳 Pay Now
                        </button>
                        <button type="button" onclick="closePaymentModal()" 
                                class="bg-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-400 smooth-transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function formatCardNumber(input) {
    let value = input.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    input.value = formattedValue;
}

function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    input.value = value;
}

function processPayment() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="loading-dots">Processing</span>';
    button.disabled = true;
    button.classList.add('animate-pulse');
    
    // Simulate payment processing
    setTimeout(() => {
        // Show success
        button.innerHTML = '✅ Payment Successful!';
        button.classList.remove('animate-pulse');
        button.classList.add('bg-green-600');
        
        setTimeout(() => {
            // Process the actual checkout
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="checkout" value="1">';
            document.body.appendChild(form);
            form.submit();
        }, 1500);
    }, 2000);
}
</script>

<?php require_once 'includes/footer.php'; ?>