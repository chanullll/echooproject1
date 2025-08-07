<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.username as seller_name, u.full_name as seller_full_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.id = ? AND p.is_approved = true
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

$page_title = $product['name'];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (isLoggedIn()) {
        $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
        if (addToCart($pdo, $product_id, $quantity)) {
            $success_message = "Product added to cart successfully!";
        } else {
            $error_message = "Failed to add product to cart.";
        }
    } else {
        header('Location: login.php');
        exit();
    }
}

// Get related products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.is_approved = true 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="index.php" class="text-gray-700 hover:text-eco-green">Home</a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-gray-400">/</span>
                    <a href="products.php" class="text-gray-700 hover:text-eco-green">Products</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2 text-gray-400">/</span>
                    <a href="products.php?category=<?php echo $product['category_id']; ?>" class="text-gray-700 hover:text-eco-green">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-500"><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($success_message); ?>
            <a href="cart.php" class="font-medium underline ml-2">View Cart</a>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Product Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Product Image -->
        <div>
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="w-full h-96 object-cover rounded-lg shadow-md">
        </div>

        <!-- Product Info -->
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="bg-eco-light text-eco-dark px-3 py-1 rounded-full text-sm font-medium">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    🌱 Saves <?php echo formatCarbon($product['carbon_saved']); ?>
                </span>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <div class="text-4xl font-bold text-eco-green mb-6">
                <?php echo formatCurrency($product['price']); ?>
            </div>

            <div class="prose max-w-none mb-6">
                <p class="text-gray-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            </div>

            <!-- Environmental Impact -->
            <div class="bg-eco-light p-4 rounded-lg mb-6">
                <h3 class="font-semibold text-eco-dark mb-2">🌍 Environmental Impact</h3>
                <p class="text-eco-dark text-sm">
                    By choosing this product, you'll save <strong><?php echo formatCarbon($product['carbon_saved']); ?></strong> 
                    compared to conventional alternatives. Every sustainable choice counts!
                </p>
            </div>

            <!-- Seller Info -->
            <div class="border-t pt-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-2">Seller Information</h3>
                <p class="text-gray-600">
                    <span class="font-medium"><?php echo htmlspecialchars($product['seller_full_name']); ?></span>
                    (@<?php echo htmlspecialchars($product['seller_name']); ?>)
                </p>
            </div>

            <!-- Add to Cart Form -->
            <?php if (isLoggedIn()): ?>
                <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <label for="quantity" class="font-medium text-gray-700">Quantity:</label>
                        <select name="quantity" id="quantity" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green smooth-transition">
                            <?php for ($i = 1; $i <= min(10, $product['stock_quantity']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <span class="text-sm text-gray-500">
                            (<?php echo $product['stock_quantity']; ?> available)
                        </span>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" name="add_to_cart" 
                                class="add-to-cart-btn flex-1 bg-eco-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-eco-dark smooth-transition btn-ripple hover-glow"
                                data-product-id="<?php echo $product_id; ?>"
                                data-quantity="1">
                            🛒 Add to Cart
                        </button>
                        <button type="button" 
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 smooth-transition">
                            ❤️ Wishlist
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="space-y-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p class="font-semibold">Out of Stock</p>
                        <p class="text-sm">This product is currently unavailable.</p>
                    </div>
                    <div class="flex space-x-4">
                        <button disabled class="flex-1 bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                            Out of Stock
                        </button>
                        <button type="button" 
                                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 smooth-transition">
                            ❤️ Wishlist
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="space-y-4">
                    <p class="text-gray-600">Please log in to purchase this product.</p>
                    <div class="flex space-x-4">
                        <a href="login.php" class="flex-1 bg-eco-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-eco-dark smooth-transition text-center">
                            Login to Buy
                        </a>
                        <a href="register.php" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 smooth-transition">
                            Register
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <script>
            // Update quantity for add to cart button
            document.getElementById('quantity').addEventListener('change', function() {
                const addToCartBtn = document.querySelector('.add-to-cart-btn');
                if (addToCartBtn) {
                    addToCartBtn.setAttribute('data-quantity', this.value);
                }
            });
            </script>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="border-t pt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($related_products as $related): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <a href="product.php?id=<?php echo $related['id']; ?>">
                    <img src="<?php echo htmlspecialchars($related['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($related['name']); ?>" 
                         class="w-full h-48 object-cover hover:scale-105 transition-transform">
                </a>
                
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        <a href="product.php?id=<?php echo $related['id']; ?>" class="hover:text-eco-green transition-colors">
                            <?php echo htmlspecialchars($related['name']); ?>
                        </a>
                    </h3>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-eco-green"><?php echo formatCurrency($related['price']); ?></span>
                        <span class="text-xs text-gray-500">🌱 <?php echo formatCarbon($related['carbon_saved']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>