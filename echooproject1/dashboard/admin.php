<?php
// Admin Dashboard Content

// Handle user management actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Prevent admin from deleting themselves
        if ($user_id !== $_SESSION['user_id']) {
            try {
                // Delete user and all related data (cascading deletes will handle most)
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                if ($stmt->execute([$user_id])) {
                    $success_message = "User deleted successfully!";
                } else {
                    $error_message = "Failed to delete user.";
                }
            } catch (Exception $e) {
                $error_message = "Error deleting user: " . $e->getMessage();
            }
        } else {
            $error_message = "You cannot delete your own account.";
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $product_id = (int)$_POST['product_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$product_id])) {
                $success_message = "Product deleted successfully!";
            } else {
                $error_message = "Failed to delete product.";
            }
        } catch (Exception $e) {
            $error_message = "Error deleting product: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['approve_product'])) {
        $product_id = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("UPDATE products SET is_approved = true WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success_message = "Product approved successfully!";
        }
    }

    if (isset($_POST['reject_product'])) {
        $product_id = (int)$_POST['product_id'];
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success_message = "Product rejected and removed.";
        }
    }
}

// Get overall stats
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_products FROM products WHERE is_approved = false");
$pending_products = $stmt->fetch()['pending_products'];

$stmt = $pdo->query("SELECT SUM(total_carbon_saved) as total_carbon FROM users");
$total_carbon = $stmt->fetch()['total_carbon'] ?? 0;

$stmt = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

// Get pending products
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, u.username as seller_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.is_approved = false 
    ORDER BY p.created_at DESC
");
$pending_products_list = $stmt->fetchAll();

// Get all users for management
$stmt = $pdo->query("
    SELECT * FROM users 
    WHERE role != 'admin' 
    ORDER BY role, created_at DESC
");
$all_users = $stmt->fetchAll();

// Get all products for management
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, u.username as seller_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    JOIN users u ON p.seller_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 20
");
$all_products = $stmt->fetchAll();

// Get top performers
$stmt = $pdo->query("
    SELECT username, full_name, total_carbon_saved, role 
    FROM users 
    WHERE role != 'admin' 
    ORDER BY total_carbon_saved DESC 
    LIMIT 5
");
$top_performers = $stmt->fetchAll();
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
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">👥</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Users</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $total_users; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">📦</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Products</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $total_products; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">⏳</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                <p class="text-2xl font-bold text-yellow-600"><?php echo $pending_products; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">🌱</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total CO₂ Saved</p>
                <p class="text-2xl font-bold text-eco-green"><?php echo formatCarbon($total_carbon); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="text-3xl mr-4">💰</div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($total_revenue); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- User Management -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">User Management</h2>
        
        <div class="space-y-4 max-h-96 overflow-y-auto">
            <?php foreach ($all_users as $user): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-sm text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-xs text-green-600">🌱 <?php echo formatCarbon($user['total_carbon_saved']); ?></p>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs rounded-full <?php 
                            echo $user['role'] === 'seller' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; 
                        ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user" 
                                    class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Pending Products -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Pending Product Approvals</h2>
        
        <?php if (!empty($pending_products_list)): ?>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                <?php foreach ($pending_products_list as $product): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-16 h-16 object-cover rounded-lg">
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-sm text-gray-600">by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="font-semibold text-eco-green"><?php echo formatCurrency($product['price']); ?></span>
                                <span class="text-sm text-green-600">🌱 <?php echo formatCarbon($product['carbon_saved']); ?></span>
                            </div>
                            
                            <div class="flex space-x-2 mt-3">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="approve_product" 
                                            class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition-colors">
                                        Approve
                                    </button>
                                </form>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="reject_product" 
                                            onclick="return confirm('Are you sure you want to reject this product?')"
                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <div class="text-4xl mb-2">✅</div>
                <p class="text-gray-600">No pending products</p>
                <p class="text-sm text-gray-500">All products have been reviewed!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Management -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Product Management</h2>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($all_products as $product): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-10 h-10 object-cover rounded-lg mr-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($product['seller_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo formatCurrency($product['price']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full <?php echo $product['is_approved'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                            <?php echo $product['is_approved'] ? 'Approved' : 'Pending'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="delete_product" 
                                    class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top Performers -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Top Eco Performers</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <?php foreach ($top_performers as $index => $performer): ?>
        <div class="text-center p-4 border border-gray-200 rounded-lg">
            <div class="text-2xl mb-2">
                <?php 
                    $medals = ['🥇', '🥈', '🥉', '🏅', '🏅'];
                    echo $medals[$index] ?? '🏅';
                ?>
            </div>
            <h3 class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($performer['full_name']); ?></h3>
            <p class="text-xs text-gray-600">@<?php echo htmlspecialchars($performer['username']); ?></p>
            <p class="text-sm font-bold text-eco-green mt-1"><?php echo formatCarbon($performer['total_carbon_saved']); ?></p>
            <span class="text-xs px-2 py-1 rounded-full <?php 
                echo $performer['role'] === 'seller' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; 
            ?>">
                <?php echo ucfirst($performer['role']); ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>