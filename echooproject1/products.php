<?php
$page_title = 'Products';
require_once 'includes/header.php';

try {
// Get filter parameters
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';

// Build query
$where_conditions = ["p.is_approved = true"];
$params = [];

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = "(p.name ILIKE ? OR p.description ILIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$where_clause = implode(" AND ", $where_conditions);

// Sort options
$order_by = "p.created_at DESC";
switch ($sort_by) {
    case 'price_low':
        $order_by = "p.price ASC";
        break;
    case 'price_high':
        $order_by = "p.price DESC";
        break;
    case 'carbon_high':
        $order_by = "p.carbon_saved DESC";
        break;
    case 'name':
        $order_by = "p.name ASC";
        break;
}

// Get products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.username as seller_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE $where_clause 
    ORDER BY $order_by
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Products page error: " . $e->getMessage());
    $products = [];
    $categories = [];
    $error_message = "Unable to load products. Please try again later.";
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Sustainable Products</h1>
        <p class="text-lg text-gray-600">Discover eco-friendly products that help save the planet</p>
    </div>

    <!-- Error Message -->
    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search_query); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green">
            </div>
            
            <!-- Category Filter -->
            <div>
                <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Sort -->
            <div>
                <select name="sort" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-eco-green focus:border-eco-green">
                    <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="carbon_high" <?php echo $sort_by === 'carbon_high' ? 'selected' : ''; ?>>Most CO₂ Saved</option>
                    <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                </select>
            </div>
            
            <button type="submit" class="bg-eco-green text-white px-6 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Results Count -->
    <div class="mb-6">
        <p class="text-gray-600">
            Found <?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?>
            <?php if ($search_query): ?>
                for "<?php echo htmlspecialchars($search_query); ?>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your search criteria or browse all categories.</p>
            <a href="products.php" class="bg-eco-green text-white px-6 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                View All Products
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <a href="product.php?id=<?php echo $product['id']; ?>">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="w-full h-48 object-cover hover:scale-105 transition-transform">
                </a>
                
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-eco-green font-medium bg-eco-light px-2 py-1 rounded">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                            🌱 <?php echo formatCarbon($product['carbon_saved']); ?>
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="hover:text-eco-green transition-colors">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                    </p>
                    
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xl font-bold text-eco-green"><?php echo formatCurrency($product['price']); ?></span>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">by <?php echo htmlspecialchars($product['seller_name']); ?></span>
                            <p class="text-xs text-gray-400">Stock: <?php echo $product['stock_quantity']; ?></p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <?php if (isLoggedIn()): ?>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="add-to-cart-btn flex-1 bg-eco-green text-white px-3 py-2 rounded-lg hover:bg-eco-dark smooth-transition text-sm btn-ripple hover-glow" 
                                    data-product-id="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="flex-1 bg-gray-400 text-white px-3 py-2 rounded-lg cursor-not-allowed text-sm" disabled>
                                Out of Stock
                            </button>
                        <?php endif; ?>
                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                           class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 smooth-transition text-sm">
                            View
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="flex-1 bg-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-400 smooth-transition text-sm text-center">
                            Login to Buy
                        </a>
                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                           class="bg-eco-green text-white px-3 py-2 rounded-lg hover:bg-eco-dark smooth-transition text-sm">
                            View
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>