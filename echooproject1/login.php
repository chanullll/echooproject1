<?php
$page_title = 'Login';
require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    redirectByRole();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Debug: Check what we're searching for
            error_log("Login attempt - Username/Email: " . $username);
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            // Debug: Check if user was found
            if ($user) {
                error_log("User found - ID: " . $user['id'] . ", Username: " . ($user['username'] ?? 'NULL') . ", Role: " . $user['role']);
            } else {
                error_log("No user found with username/email: " . $username);
                
                // Additional debug: Check what users exist
                $debug_stmt = $pdo->query("SELECT id, username, email, name FROM users LIMIT 5");
                $debug_users = $debug_stmt->fetchAll();
                error_log("Available users: " . print_r($debug_users, true));
            }
            
            if ($user && password_verify($password, $user['password'])) {
                error_log("Password verified successfully for user: " . $username);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'] ?? $user['name'] ?? $username;
                
                redirectByRole();
            } else {
                if ($user) {
                    error_log("Password verification failed for user: " . $username);
                } else {
                    error_log("User not found: " . $username);
                }
                $error = 'Invalid username/email or password.';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Login failed. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="text-center">
                <span class="text-6xl">🌱</span>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Sign in to EcoStore
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Welcome back, eco-warrior!
                </p>
            </div>
        </div>
        
        <form class="mt-8 space-y-6" method="POST">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        Username or Email
                    </label>
                    <input id="username" name="username" type="text" required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green focus:z-10 sm:text-sm" 
                           placeholder="Enter your username or email"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input id="password" name="password" type="password" required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-eco-green focus:border-eco-green focus:z-10 sm:text-sm" 
                           placeholder="Enter your password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-eco-green hover:bg-eco-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-eco-green transition-colors">
                    Sign in
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="font-medium text-eco-green hover:text-eco-dark">
                        Register here
                    </a>
                </p>
            </div>
        </form>
        
        <!-- Demo Accounts -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-sm font-medium text-blue-900 mb-2">Demo Accounts:</h3>
            <div class="text-xs text-blue-700 space-y-1">
                <p><strong>Admin:</strong> admin / admin123</p>
                <p><strong>Seller:</strong> greenseller / seller123</p>
                <p><strong>Buyer:</strong> ecobuyer / buyer123</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>