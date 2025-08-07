<?php
// Seller Dashboard Content

// Get seller stats
$stmt = $pdo->prepare("SELECT COUNT(*) as product_count FROM products WHERE seller_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$product_count = $stmt->fetch()['product_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as approved_count FROM products WHERE seller_id = ? AND is_approved = true");
$stmt->execute([$_SESSION['user_id']]);
$approved_count = $stmt->fetch()['approved_count'];

$stmt = $pdo->prepare("
    SELECT SUM(oi.price * oi.quantity) as total_sales 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE p.seller_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_sales = $stmt->fetch()['total_sales'] ?? 0;

$stmt = $pdo->prepare("
    SELECT SUM(oi.carbon_saved * oi.quantity) as total_impact 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE p.seller_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$total_impact = $stmt->fetch()['total_impact'] ?? 0;

// Get seller's purchase history (when they buy as customers)
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$seller_orders = $stmt->fetchAll();

// Get recent products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.seller_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_products = $stmt->fetchAll();

// Handle new product form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $carbon_saved = (float)$_POST['carbon_saved'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $image_url = sanitizeInput($_POST['image_url']);
    
    if (!empty($name) && $price > 0 && $category_id > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, category_id, seller_id, carbon_saved, stock_quantity, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $description, $price, $category_id, $_SESSION['user_id'], $carbon_saved, $stock_quantity, $image_url])) {
            $success_message = "Product added successfully! It will be reviewed by admin before going live.";
        } else {
            $error_message = "Failed to add product.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = (int)$_POST['product_id'];
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = (float)$_POST['price'];
    $carbon_saved = (float)$_POST['carbon_saved'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $image_url = sanitizeInput($_POST['image_url']);
    
    if (!empty($name) && $price > 0) {
        // Verify the product belongs to this seller
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, carbon_saved = ?, stock_quantity = ?, image_url = ?
                WHERE id = ? AND seller_id = ?
            ");
            
            if ($stmt->execute([$name, $description, $price, $carbon_saved, $stock_quantity, $image_url, $product_id, $_SESSION['user_id']])) {
                $success_message = "Product updated successfully!";
            } else {
                $error_message = "Failed to update product.";
            }
        } else {
            $error_message = "Product not found or you don't have permission to edit it.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Get categories for form
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">📦</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Products</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $product_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">✅</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Approved Products</p>
                <p class="text-2xl font-bold text-eco-green"><?php echo $approved_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">💰</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Sales</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($total_sales); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🌱</div>
            <div>
                <p class="text-sm font-medium text-gray-600">CO₂ Impact</p>
                <p class="text-2xl font-bold text-eco-green"><?php echo formatCarbon($total_impact); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Add New Product -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Add New Product</h2>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                <input type="text" id="name" name="name" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3" 
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                </div>
                
                <div>
                    <label for="carbon_saved" class="block text-sm font-medium text-gray-700">CO₂ Saved (kg)</label>
                    <input type="number" id="carbon_saved" name="carbon_saved" step="0.1" min="0" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category_id" name="category_id" required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="1" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                </div>
            </div>
            
            <div>
                <label for="image_url" class="block text-sm font-medium text-gray-700">Image URL</label>
                <input type="url" id="image_url" name="image_url" 
                       placeholder="https://images.pexels.com/..."
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
            </div>
            
            <button type="submit" name="add_product" 
                    class="w-full bg-eco-green text-white py-2 px-4 rounded-md hover:bg-eco-dark transition-colors">
                Add Product
            </button>
        </form>
    </div>
    
    <!-- Recent Products -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Your Products</h2>
            <a href="#" class="text-eco-green hover:text-eco-dark text-sm">View All</a>
        </div>
        
        <?php if (!empty($recent_products)): ?>
            <div class="space-y-4">
                <?php foreach ($recent_products as $product): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-16 h-16 object-cover rounded-lg">
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <p class="text-xs text-gray-500">Stock: <?php echo $product['stock_quantity']; ?></p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="font-semibold text-eco-green"><?php echo formatCurrency($product['price']); ?></span>
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $product['is_approved'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $product['is_approved'] ? 'Approved' : 'Pending'; ?>
                                </span>
                            </div>
                            <div class="mt-2 flex space-x-2">
                                <a href="manage_products.php" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition-colors">
                                    Manage Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-4xl mb-2">📦</div>
                <p class="text-gray-600">No products yet</p>
                <p class="text-sm text-gray-500">Add your first sustainable product above!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Purchase History for Seller -->
<div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Your Purchase History</h2>
    
    <?php if (!empty($seller_orders)): ?>
        <div class="space-y-4">
            <?php foreach ($seller_orders as $order): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-gray-900">Order #<?php echo $order['id']; ?></p>
                        <p class="text-sm text-gray-600"><?php echo $order['item_count']; ?> item(s) - <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-eco-green"><?php echo formatCurrency($order['total_amount']); ?></p>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <div class="text-4xl mb-2">🛍️</div>
            <p class="text-gray-600">No purchases yet</p>
            <p class="text-sm text-gray-500">Shop from other sellers to see your purchase history here</p>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Seller Resources</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 border border-gray-200 rounded-lg">
            <div class="text-2xl mb-2">📊</div>
            <h3 class="font-semibold text-gray-900">Sales Analytics</h3>
            <p class="text-sm text-gray-600">Track your product performance</p>
        </div>
        
        <div class="p-4 border border-gray-200 rounded-lg">
            <div class="text-2xl mb-2">🌱</div>
            <h3 class="font-semibold text-gray-900">Sustainability Tips</h3>
            <p class="text-sm text-gray-600">Learn how to improve your eco-impact</p>
        </div>
        
        <div class="p-4 border border-gray-200 rounded-lg">
            <div class="text-2xl mb-2">💬</div>
            <h3 class="font-semibold text-gray-900">Customer Support</h3>
            <p class="text-sm text-gray-600">Get help with your seller account</p>
        </div>
    </div>
</div>