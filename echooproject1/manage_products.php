<?php
$page_title = 'Manage Products';
require_once 'includes/header.php';

requireLogin();

// Only sellers can access this page
if (!hasRole('seller')) {
    header('Location: dashboard.php');
    exit();
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Verify the product belongs to this seller
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        if ($stmt->execute([$product_id, $_SESSION['user_id']])) {
            $success_message = "Product deleted successfully!";
        } else {
            $error_message = "Failed to delete product.";
        }
    } else {
        $error_message = "Product not found or you don't have permission to delete it.";
    }
}

// Get seller's products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.seller_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();

// Get categories for the edit form
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Your Products</h1>
        <a href="dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
            Back to Dashboard
        </a>
    </div>

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

    <!-- Products List -->
    <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No products yet</h3>
            <p class="text-gray-600 mb-4">Start by adding your first sustainable product.</p>
            <a href="dashboard.php" class="bg-eco-green text-white px-6 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                Add Product
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="w-full h-48 object-cover">
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-eco-green font-medium bg-eco-light px-2 py-1 rounded">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                        <span class="px-2 py-1 text-xs rounded-full <?php echo $product['is_approved'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                            <?php echo $product['is_approved'] ? 'Approved' : 'Pending'; ?>
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                    </p>
                    
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xl font-bold text-eco-green"><?php echo formatCurrency($product['price']); ?></span>
                        <span class="text-sm text-gray-500">Stock: <?php echo $product['stock_quantity']; ?></span>
                    </div>
                    
                    <div class="flex gap-2">
                        <button onclick="editProduct(<?php echo $product['id']; ?>)" 
                                class="flex-1 bg-blue-500 text-white px-3 py-2 rounded-lg hover:bg-blue-600 transition-colors text-sm">
                            Edit
                        </button>
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="delete_product" 
                                    class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Product Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Edit Product</h2>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="editForm" method="POST" action="dashboard.php" class="space-y-4">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                        <input type="text" id="edit_name" name="name" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                    </div>
                    
                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="edit_description" name="description" rows="3" 
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_price" class="block text-sm font-medium text-gray-700">Price ($) *</label>
                            <input type="number" id="edit_price" name="price" step="0.01" min="0" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                        </div>
                        
                        <div>
                            <label for="edit_carbon_saved" class="block text-sm font-medium text-gray-700">CO₂ Saved (kg)</label>
                            <input type="number" id="edit_carbon_saved" name="carbon_saved" step="0.1" min="0" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                        </div>
                    </div>
                    
                    <div>
                        <label for="edit_stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                        <input type="number" id="edit_stock_quantity" name="stock_quantity" min="0" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                    </div>
                    
                    <div>
                        <label for="edit_image_url" class="block text-sm font-medium text-gray-700">Image URL</label>
                        <input type="url" id="edit_image_url" name="image_url" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green">
                    </div>
                    
                    <div class="flex gap-4 pt-4">
                        <button type="submit" name="update_product" 
                                class="flex-1 bg-eco-green text-white py-2 px-4 rounded-md hover:bg-eco-dark transition-colors">
                            Update Product
                        </button>
                        <button type="button" onclick="closeEditModal()" 
                                class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Product data for editing
const products = <?php echo json_encode($products); ?>;

function editProduct(productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return;
    
    // Fill the form
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_carbon_saved').value = product.carbon_saved;
    document.getElementById('edit_stock_quantity').value = product.stock_quantity;
    document.getElementById('edit_image_url').value = product.image_url || '';
    
    // Show modal
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>