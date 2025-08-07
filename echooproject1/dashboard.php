<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

requireLogin();

try {
    $current_user = getCurrentUser($pdo);
    if (!$current_user) {
        header('Location: login.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    header('Location: login.php');
    exit();
}

$view = isset($_GET['view']) ? sanitizeInput($_GET['view']) : $current_user['role'];

// Ensure user can only access their own dashboard or admin can access all
if ($view !== $current_user['role'] && $current_user['role'] !== 'admin') {
    $view = $current_user['role'];
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Dashboard Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($current_user['full_name']); ?>!</p>
    </div>

    <!-- Role Tabs (for admin) -->
    <?php if ($current_user['role'] === 'admin'): ?>
    <div class="mb-8">
        <nav class="flex space-x-8">
            <a href="?view=admin" class="<?php echo $view === 'admin' ? 'border-eco-green text-eco-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Admin Panel
            </a>
            <a href="?view=seller" class="<?php echo $view === 'seller' ? 'border-eco-green text-eco-green' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Seller View
            </a>
        </nav>
    </div>
    <?php endif; ?>

    <?php if ($view === 'seller'): ?>
        <?php include 'dashboard/seller.php'; ?>
    <?php else: ?>
        <?php include 'dashboard/admin.php'; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>