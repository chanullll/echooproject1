<?php
$page_title = 'Eco Leaderboard';
require_once 'includes/header.php';

try {
    // Get top users by carbon saved
    $stmt = $pdo->query("
        SELECT u.*, 
               ROW_NUMBER() OVER (ORDER BY u.total_carbon_saved DESC) as rank
        FROM users u 
        WHERE u.role != 'admin' AND u.total_carbon_saved > 0
        ORDER BY u.total_carbon_saved DESC 
        LIMIT 50
    ");
    $leaderboard = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Leaderboard query error: " . $e->getMessage());
    $leaderboard = [];
}

// Get current user's rank if logged in
$current_user_rank = null;
if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("
            SELECT rank FROM (
                SELECT u.id, 
                       ROW_NUMBER() OVER (ORDER BY u.total_carbon_saved DESC) as rank
                FROM users u 
                WHERE u.role != 'admin' AND u.total_carbon_saved > 0
            ) ranked 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $current_user_rank = $result['rank'] ?? null;
    } catch (Exception $e) {
        error_log("User rank query error: " . $e->getMessage());
        $current_user_rank = null;
    }
}

// Get some stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_participants FROM users WHERE role != 'admin' AND total_carbon_saved > 0");
    $total_participants = $stmt->fetch()['total_participants'];

    $stmt = $pdo->query("SELECT SUM(total_carbon_saved) as total_saved FROM users WHERE role != 'admin'");
    $total_saved = $stmt->fetch()['total_saved'] ?? 0;
} catch (Exception $e) {
    error_log("Stats query error: " . $e->getMessage());
    $total_participants = 0;
    $total_saved = 0;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">🏆 Eco Leaderboard</h1>
        <p class="text-lg text-gray-600 mb-6">
            Celebrating our community's environmental impact
        </p>
        
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
            <div class="bg-eco-light p-4 rounded-lg">
                <div class="text-2xl font-bold text-eco-dark"><?php echo $total_participants; ?></div>
                <div class="text-sm text-eco-dark">Active Eco-Warriors</div>
            </div>
            <div class="bg-eco-light p-4 rounded-lg">
                <div class="text-2xl font-bold text-eco-dark"><?php echo formatCarbon($total_saved); ?></div>
                <div class="text-sm text-eco-dark">Total CO₂ Saved</div>
            </div>
            <div class="bg-eco-light p-4 rounded-lg">
                <div class="text-2xl font-bold text-eco-dark"><?php echo number_format($total_saved / max(1, $total_participants), 1); ?> kg</div>
                <div class="text-sm text-eco-dark">Average per Person</div>
            </div>
        </div>
    </div>

    <!-- Current User Rank -->
    <?php if (isLoggedIn() && $current_user_rank): ?>
    <div class="bg-gradient-to-r from-eco-green to-eco-dark text-white rounded-lg p-6 mb-8">
        <div class="text-center">
            <h2 class="text-xl font-bold mb-2">Your Current Rank</h2>
            <div class="text-4xl font-bold mb-2">#<?php echo $current_user_rank; ?></div>
            <p class="text-eco-light">
                You've saved <?php echo formatCarbon($current_user['total_carbon_saved']); ?> so far!
            </p>
            <?php if ($current_user_rank > 10): ?>
            <p class="text-sm text-eco-light mt-2">
                Keep shopping sustainably to climb the leaderboard! 🌱
            </p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leaderboard -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Top Eco-Champions</h2>
        </div>
        
        <?php if (empty($leaderboard)): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🌱</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No eco-champions yet!</h3>
                <p class="text-gray-600 mb-4">Be the first to make a sustainable purchase and claim the top spot.</p>
                <a href="products.php" class="bg-eco-green text-white px-6 py-2 rounded-lg hover:bg-eco-dark transition-colors">
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($leaderboard as $index => $user): ?>
                <div class="px-6 py-4 flex items-center <?php echo isLoggedIn() && $user['id'] == $_SESSION['user_id'] ? 'bg-eco-light' : ''; ?>">
                    <!-- Rank -->
                    <div class="flex-shrink-0 w-16 text-center">
                        <?php if ($user['rank'] <= 3): ?>
                            <div class="text-3xl">
                                <?php 
                                    $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
                                    echo $medals[$user['rank']];
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="text-2xl font-bold text-gray-600">#<?php echo $user['rank']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex-1 ml-4">
                        <div class="flex items-center">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                                <?php if (isLoggedIn() && $user['id'] == $_SESSION['user_id']): ?>
                                    <span class="text-eco-green text-sm">(You)</span>
                                <?php endif; ?>
                            </h3>
                            <span class="ml-2 px-2 py-1 text-xs rounded-full <?php 
                                echo $user['role'] === 'seller' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; 
                            ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    
                    <!-- Carbon Saved -->
                    <div class="text-right">
                        <div class="text-xl font-bold text-eco-green">
                            <?php echo formatCarbon($user['total_carbon_saved']); ?>
                        </div>
                        <div class="text-sm text-gray-500">CO₂ saved</div>
                    </div>
                    
                    <!-- Achievement Level -->
                    <div class="ml-6 text-center">
                        <?php
                        $carbon = $user['total_carbon_saved'];
                        if ($carbon >= 250) {
                            echo '<div class="text-2xl">🌟</div><div class="text-xs text-gray-600">Earth Guardian</div>';
                        } elseif ($carbon >= 100) {
                            echo '<div class="text-2xl">🏆</div><div class="text-xs text-gray-600">Climate Champion</div>';
                        } elseif ($carbon >= 50) {
                            echo '<div class="text-2xl">🌍</div><div class="text-xs text-gray-600">Planet Protector</div>';
                        } elseif ($carbon >= 25) {
                            echo '<div class="text-2xl">🌿</div><div class="text-xs text-gray-600">Eco Warrior</div>';
                        } else {
                            echo '<div class="text-2xl">🌱</div><div class="text-xs text-gray-600">Green Beginner</div>';
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <?php if (!isLoggedIn()): ?>
    <div class="mt-8 text-center bg-eco-green text-white rounded-lg p-8">
        <h2 class="text-2xl font-bold mb-4">Join the Eco Revolution!</h2>
        <p class="text-eco-light mb-6">
            Create an account and start making sustainable purchases to appear on the leaderboard.
        </p>
        <div class="flex justify-center space-x-4">
            <a href="register.php" class="bg-white text-eco-green px-6 py-3 rounded-lg font-semibold hover:bg-eco-light transition-colors">
                Sign Up Now
            </a>
            <a href="login.php" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-eco-green transition-colors">
                Login
            </a>
        </div>
    </div>
    <?php elseif ($current_user['total_carbon_saved'] == 0): ?>
    <div class="mt-8 text-center bg-eco-green text-white rounded-lg p-8">
        <h2 class="text-2xl font-bold mb-4">Start Your Eco Journey!</h2>
        <p class="text-eco-light mb-6">
            Make your first sustainable purchase to join the leaderboard and start saving the planet.
        </p>
        <a href="products.php" class="bg-white text-eco-green px-6 py-3 rounded-lg font-semibold hover:bg-eco-light transition-colors">
            Browse Eco Products
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>