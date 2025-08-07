<?php

$page_title = 'Home';
require_once 'includes/header.php';

// Handle database connection errors gracefully
try {
// Get some stats for the hero section
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'buyer'");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products WHERE is_approved = true");
$total_products = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT SUM(total_carbon_saved) as total_carbon FROM users");
$total_carbon = $stmt->fetch()['total_carbon'] ?? 0;

// Get featured products
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, u.username as seller_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.is_approved = true 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
$featured_products = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Homepage database error: " . $e->getMessage());
    $total_users = 0;
    $total_products = 0;
    $total_carbon = 0;
    $featured_products = [];
}
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-eco-green to-eco-dark text-white py-20 animate-fade-in-up">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center animate-scale-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in-up">
                Shop Sustainably,<br>
                <span class="text-eco-light animate-pulse">Save the Planet</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-eco-light max-w-3xl mx-auto animate-fade-in-up" style="animation-delay: 0.2s;">
                Join thousands of eco-conscious shoppers making a difference. Every purchase helps reduce carbon emissions and protects our environment.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay: 0.4s;">
                <a href="products.php" class="bg-white text-eco-green px-8 py-4 rounded-lg font-semibold hover:bg-eco-light smooth-transition btn-ripple hover-lift">
                    Shop Now 🛒
                </a>
                <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-eco-green smooth-transition btn-ripple hover-lift">
                    Join EcoStore 🌱
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Impact Stats -->
<section class="py-16 bg-white animate-on-scroll">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Environmental Impact</h2>
            <p class="text-lg text-gray-600">Together, we're making a real difference</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 stagger-animation">
            <div class="text-center p-6 bg-eco-light rounded-lg hover-lift card-hover">
                <div class="text-4xl mb-4 animate-bounce">👥</div>
                <div class="text-3xl font-bold text-eco-dark mb-2"><?php echo number_format($total_users); ?></div>
                <div class="text-gray-700">Eco-Conscious Shoppers</div>
            </div>
            
            <div class="text-center p-6 bg-eco-light rounded-lg hover-lift card-hover">
                <div class="text-4xl mb-4 animate-pulse">🌍</div>
                <div class="text-3xl font-bold text-eco-dark mb-2"><?php echo formatCarbon($total_carbon); ?></div>
                <div class="text-gray-700">Carbon Emissions Saved</div>
            </div>
            
            <div class="text-center p-6 bg-eco-light rounded-lg hover-lift card-hover">
                <div class="text-4xl mb-4 animate-bounce" style="animation-delay: 0.5s;">🛍️</div>
                <div class="text-3xl font-bold text-eco-dark mb-2"><?php echo number_format($total_products); ?></div>
                <div class="text-gray-700">Sustainable Products</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 bg-gray-50 animate-on-scroll">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Eco-Products</h2>
            <p class="text-lg text-gray-600">Discover sustainable products that make a difference</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 stagger-animation">
            <?php foreach ($featured_products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover-lift card-hover">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="w-full h-48 object-cover hover-scale smooth-transition">
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-eco-green font-medium bg-eco-light px-2 py-1 rounded animate-fade-in-left"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <span class="text-sm text-gray-500 animate-fade-in-right">🌱 <?php echo formatCarbon($product['carbon_saved']); ?></span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="hover:text-eco-green smooth-transition">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                    </p>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-eco-green"><?php echo formatCurrency($product['price']); ?></span>
                        <?php if (isLoggedIn()): ?>
                        <button class="add-to-cart-btn bg-eco-green text-white px-4 py-2 rounded-lg hover:bg-eco-dark smooth-transition btn-ripple hover-glow" 
                                data-product-id="<?php echo $product['id']; ?>">
                            Add to Cart
                        </button>
                        <?php else: ?>
                        <a href="login.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 smooth-transition">
                            Login to Buy
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="products.php" class="bg-eco-green text-white px-8 py-3 rounded-lg font-semibold hover:bg-eco-dark smooth-transition btn-ripple hover-glow animate-bounce">
                View All Products
            </a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-16 bg-white animate-on-scroll" id="how-it-works">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">How EcoStore Works</h2>
            <p class="text-lg text-gray-600">Simple steps to make a positive environmental impact</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 stagger-animation">
            <div class="text-center hover-lift">
                <div class="bg-eco-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 hover-scale animate-bounce">
                    <span class="text-2xl">🛍️</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Shop Sustainable</h3>
                <p class="text-gray-600">Browse our curated selection of eco-friendly products from verified sustainable sellers.</p>
            </div>
            
            <div class="text-center hover-lift">
                <div class="bg-eco-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 hover-scale animate-pulse">
                    <span class="text-2xl">🌱</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Save Carbon</h3>
                <p class="text-gray-600">Every purchase shows exactly how much CO₂ you're saving compared to traditional alternatives.</p>
            </div>
            
            <div class="text-center hover-lift">
                <div class="bg-eco-light rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 hover-scale animate-bounce" style="animation-delay: 1s;">
                    <span class="text-2xl">🏆</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Earn Badges</h3>
                <p class="text-gray-600">Get recognized for your environmental impact with badges and climb the eco-leaderboard.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-eco-green text-white animate-on-scroll" id="get-started">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4 animate-fade-in-up">Ready to Make a Difference?</h2>
        <p class="text-xl mb-8 text-eco-light animate-fade-in-up" style="animation-delay: 0.2s;">Join our community of eco-warriors and start your sustainable shopping journey today.</p>
        
        <?php if (isLoggedIn()): ?>
        <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay: 0.4s;">
            <a href="products.php" class="bg-white text-eco-green px-8 py-3 rounded-lg font-semibold hover:bg-eco-light smooth-transition btn-ripple hover-lift">
                Start Shopping
            </a>
            <a href="leaderboard.php" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-eco-green smooth-transition btn-ripple hover-lift">
                View Leaderboard
            </a>
        </div>
        <?php else: ?>
        <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay: 0.4s;">
            <a href="register.php" class="bg-white text-eco-green px-8 py-3 rounded-lg font-semibold hover:bg-eco-light smooth-transition btn-ripple hover-lift">
                Create Account
            </a>
            <a href="login.php" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-eco-green smooth-transition btn-ripple hover-lift">
                Sign In
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>