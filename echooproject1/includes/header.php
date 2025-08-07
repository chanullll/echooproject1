<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
startSession();
$current_user = getCurrentUser($pdo);
$cart_count = getCartCount($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - EcoStore' : 'EcoStore - Shop Sustainably, Save the Planet'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'eco-green': '#10B981',
                        'eco-dark': '#065F46',
                        'eco-light': '#D1FAE5'
                    }
                }
            }
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }
        
        /* Modern Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }
        
        @keyframes shimmer {
            0% {
                background-position: -468px 0;
            }
            100% {
                background-position: 468px 0;
            }
        }
        
        /* Animation Classes */
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-fade-in-left {
            animation: fadeInLeft 0.6s ease-out forwards;
        }
        
        .animate-fade-in-right {
            animation: fadeInRight 0.6s ease-out forwards;
        }
        
        .animate-scale-in {
            animation: scaleIn 0.5s ease-out forwards;
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        
        .animate-bounce {
            animation: bounce 1s infinite;
        }
        
        .animate-slide-down {
            animation: slideInDown 0.5s ease-out forwards;
        }
        
        .shimmer {
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 800px 104px;
            animation: shimmer 1.5s linear infinite;
        }
        
        /* Hover Effects */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .hover-scale {
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        .hover-glow {
            transition: all 0.3s ease;
        }
        
        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
        }
        
        /* Button Animations */
        .btn-ripple {
            position: relative;
            overflow: hidden;
        }
        
        .btn-ripple::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-ripple:active::before {
            width: 300px;
            height: 300px;
        }
        
        /* Loading States */
        .loading-dots {
            display: inline-block;
        }
        
        .loading-dots::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }
        
        /* Card Animations */
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Notification Animations */
        .notification-enter {
            animation: slideInRight 0.3s ease-out forwards;
        }
        
        .notification-exit {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        /* Stagger Animation */
        .stagger-animation > * {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .stagger-animation > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-animation > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-animation > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-animation > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-animation > *:nth-child(5) { animation-delay: 0.5s; }
        .stagger-animation > *:nth-child(6) { animation-delay: 0.6s; }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Loading spinner */
        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #10B981;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Smooth transitions */
        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Fix for mobile menu */
        .mobile-menu-hidden {
            transform: translateY(-100%);
            opacity: 0;
        }
        
        .mobile-menu-visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        /* Success Animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 2;
            stroke: #10B981;
            stroke-miterlimit: 10;
            margin: 10% auto;
            box-shadow: inset 0px 0px 0px #10B981;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        
        .success-checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #10B981;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        
        .success-checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        
        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }
        
        @keyframes scale {
            0%, 100% {
                transform: none;
            }
            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }
        
        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 30px #10B981;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen animate-fade-in-up" id="top">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 animate-slide-down">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center animate-fade-in-left">
                    <a href="index.php" class="flex items-center space-x-2">
                        <span class="text-2xl animate-pulse">🌱</span>
                        <span class="text-xl font-bold text-eco-green">EcoStore</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8 animate-fade-in-right">
                    <a href="index.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">Home</a>
                    <a href="products.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">Products</a>
                    <a href="leaderboard.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">Leaderboard</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">Dashboard</a>
                        <?php if (hasRole('seller')): ?>
                        <a href="manage_products.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">My Products</a>
                        <?php endif; ?>
                        <a href="cart.php" class="relative text-gray-700 hover:text-eco-green smooth-transition hover-scale">
                            🛒 Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="absolute -top-2 -right-2 bg-eco-green text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-bounce"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="orders.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">Orders</a>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($current_user['username']); ?></span>
                            <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 smooth-transition btn-ripple">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">Login</a>
                        <a href="register.php" class="bg-eco-green text-white px-4 py-2 rounded-lg hover:bg-eco-dark smooth-transition btn-ripple hover-glow">Register</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-eco-green smooth-transition hover-scale">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="md:hidden hidden pb-4 smooth-transition">
                <div class="flex flex-col space-y-2">
                    <a href="index.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">Home</a>
                    <a href="products.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">Products</a>
                    <a href="leaderboard.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">Leaderboard</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">Dashboard</a>
                        <?php if (hasRole('seller')): ?>
                        <a href="manage_products.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">My Products</a>
                        <?php endif; ?>
                        <a href="cart.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">
                            🛒 Cart <?php if ($cart_count > 0): ?>(<?php echo $cart_count; ?>)<?php endif; ?>
                        </a>
                        <a href="orders.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">Orders</a>
                        <div class="border-t pt-2">
                            <span class="text-sm text-gray-600 block py-1">Welcome, <?php echo htmlspecialchars($current_user['username']); ?></span>
                            <a href="logout.php" class="text-red-500 hover:text-red-600 smooth-transition py-2">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-eco-green smooth-transition py-2">Login</a>
                        <a href="register.php" class="bg-eco-green text-white px-4 py-2 rounded-lg hover:bg-eco-dark smooth-transition inline-block">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                setTimeout(() => {
                    mobileMenu.classList.add('mobile-menu-visible');
                }, 10);
            } else {
                mobileMenu.classList.remove('mobile-menu-visible');
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 300);
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            
            if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.remove('mobile-menu-visible');
                    setTimeout(() => {
                        mobileMenu.classList.add('hidden');
                    }, 300);
                }
            }
        });
    </script>