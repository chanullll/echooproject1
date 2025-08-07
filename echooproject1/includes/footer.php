<!-- Footer -->
    <footer class="bg-eco-dark text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">🌱</span>
                        <span class="text-xl font-bold">EcoStore</span>
                    </div>
                    <p class="text-gray-300 mb-4">Shop sustainably and make a positive impact on our planet. Every purchase helps reduce carbon emissions.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">📘</a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">🐦</a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">📷</a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="products.php" class="text-gray-300 hover:text-white transition-colors">Products</a></li>
                        <li><a href="leaderboard.php" class="text-gray-300 hover:text-white transition-colors">Leaderboard</a></li>
                        <li><a href="dashboard.php" class="text-gray-300 hover:text-white transition-colors">Dashboard</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <li><a href="products.php?category=1" class="text-gray-300 hover:text-white transition-colors">Clothing</a></li>
                        <li><a href="products.php?category=2" class="text-gray-300 hover:text-white transition-colors">Home & Garden</a></li>
                        <li><a href="products.php?category=3" class="text-gray-300 hover:text-white transition-colors">Electronics</a></li>
                        <li><a href="products.php?category=4" class="text-gray-300 hover:text-white transition-colors">Personal Care</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <div class="space-y-2 text-gray-300">
                        <p>📧 info@ecostore.com</p>
                        <p>📞 +1 (555) 123-4567</p>
                        <p>📍 123 Green Street, Eco City, EC 12345</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-600 mt-8 pt-8 text-center">
                <p class="text-gray-300">&copy; <?php echo date('Y'); ?> EcoStore. All rights reserved. Made with 💚 for the planet.</p>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced JavaScript for better interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize page
            initializePage();
            
            // Initialize animations
            initializeAnimations();
            
            // Add to cart functionality
            const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.dataset.productId;
                    const quantity = this.dataset.quantity || 1;
                    addToCart(productId, quantity, this);
                });
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Back to top functionality
            createBackToTopButton();
            
            // Form validation
            enhanceFormValidation();
            
            // Image lazy loading
            implementLazyLoading();
        });
        
        // Initialize animations
        function initializeAnimations() {
            // Intersection Observer for scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
            
            // Stagger animations for grids
            document.querySelectorAll('.stagger-animation').forEach(container => {
                const children = container.children;
                Array.from(children).forEach((child, index) => {
                    child.style.animationDelay = `${index * 0.1}s`;
                });
            });
        }
        
        // Add to cart function
        function addToCart(productId, quantity, button) {
            // Show loading state
            const originalText = button.textContent;
            button.innerHTML = '<span class="loading-dots">Adding</span>';
            button.disabled = true;
            button.classList.add('animate-pulse');
            
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('add_to_cart', '1');
            
            // Send AJAX request
            fetch('cart_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success animation
                    button.innerHTML = '<span class="animate-bounce">✓ Added!</span>';
                    button.classList.remove('bg-eco-green', 'hover:bg-eco-dark');
                    button.classList.add('bg-green-600');
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count);
                    
                    // Show success message
                    showNotification('🎉 Product added to cart!', 'success');
                } else {
                    button.innerHTML = 'Error';
                    button.classList.add('bg-red-500');
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
                
                // Reset button after delay
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                    button.classList.remove('animate-pulse');
                    button.classList.remove('bg-green-600', 'bg-red-500');
                    button.classList.add('bg-eco-green', 'hover:bg-eco-dark');
                }, 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = 'Error';
                button.classList.add('bg-red-500');
                showNotification('Network error. Please try again.', 'error');
                
                // Reset button
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                    button.classList.remove('animate-pulse');
                    button.classList.remove('bg-red-500');
                    button.classList.add('bg-eco-green', 'hover:bg-eco-dark');
                }, 2000);
            });
        }
        
        // Update cart count in header
        function updateCartCount(count) {
            const cartLinks = document.querySelectorAll('a[href="cart.php"]');
            cartLinks.forEach(link => {
                const badge = link.querySelector('.absolute');
                if (count > 0) {
                    if (badge) {
                        badge.textContent = count;
                        badge.classList.add('animate-bounce');
                        setTimeout(() => badge.classList.remove('animate-bounce'), 1000);
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'absolute -top-2 -right-2 bg-eco-green text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-scale-in';
                        newBadge.textContent = count;
                        link.appendChild(newBadge);
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
        }
        
        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 z-50 px-6 py-4 rounded-lg shadow-xl transform translate-x-full transition-all duration-500 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            
            // Add icon based on type
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <span class="text-lg">${icon}</span>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Slide in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
                notification.classList.add('animate-fade-in-right');
            }, 100);
            
            // Slide out and remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                notification.classList.add('animate-fade-out-right');
                    setTimeout(() => {
                    if (notification.parentNode) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }
        
        // Create back to top button
        function createBackToTopButton() {
            const backToTop = document.createElement('button');
            backToTop.innerHTML = '↑';
            backToTop.className = 'fixed bottom-6 right-6 bg-eco-green text-white w-12 h-12 rounded-full shadow-lg hover:bg-eco-dark smooth-transition opacity-0 pointer-events-none z-50 hover-glow';
            backToTop.setAttribute('aria-label', 'Back to top');
            
            document.body.appendChild(backToTop);
            
            // Show/hide based on scroll position
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.remove('opacity-0', 'pointer-events-none');
                    backToTop.classList.add('animate-scale-in');
                } else {
                    backToTop.classList.add('opacity-0', 'pointer-events-none');
                    backToTop.classList.remove('animate-scale-in');
                }
            });
            
            // Scroll to top when clicked
            backToTop.addEventListener('click', () => {
                backToTop.classList.add('animate-bounce');
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                setTimeout(() => backToTop.classList.remove('animate-bounce'), 1000);
            });
        }
        
        // Initialize page
        function initializePage() {
            // Add loading class to images
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                if (!img.complete) {
                    img.classList.add('opacity-0', 'shimmer');
                    img.addEventListener('load', function() {
                        this.classList.remove('opacity-0', 'shimmer');
                        this.classList.add('animate-fade-in-up');
                    });
                }
            });
            
            // Add hover effects to cards
            document.querySelectorAll('.bg-white').forEach(card => {
                if (card.classList.contains('rounded-lg') || card.classList.contains('shadow-md')) {
                    card.classList.add('hover-lift', 'smooth-transition');
                }
            });
        }
        
        // Enhanced form validation
        function enhanceFormValidation() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.addEventListener('blur', validateField);
                    input.addEventListener('input', clearFieldError);
                });
            });
        }
        
        // Validate individual field
        function validateField(event) {
            const field = event.target;
            const value = field.value.trim();
            let isValid = true;
            let message = '';
            
            // Remove existing error
            clearFieldError(event);
            
            // Required field validation
            if (field.hasAttribute('required') && !value) {
                isValid = false;
                message = 'This field is required';
            }
            
            // Email validation
            if (field.type === 'email' && value && !isValidEmail(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
            
            // Password validation
            if (field.type === 'password' && value && value.length < 6) {
                isValid = false;
                message = 'Password must be at least 6 characters';
            }
            
            if (!isValid) {
                showFieldError(field, message);
            }
        }
        
        // Clear field error
        function clearFieldError(event) {
            const field = event.target;
            const errorElement = field.parentNode.querySelector('.field-error');
            if (errorElement) {
                errorElement.remove();
            }
            field.classList.remove('border-red-500');
        }
        
        // Show field error
        function showFieldError(field, message) {
            field.classList.add('border-red-500');
            const errorElement = document.createElement('p');
            errorElement.className = 'field-error text-red-500 text-sm mt-1';
            errorElement.textContent = message;
            field.parentNode.appendChild(errorElement);
        }
        
        // Email validation helper
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Payment Modal Functions
        function openPaymentModal(amount, items) {
            const modal = document.getElementById('paymentModal');
            const totalAmount = document.getElementById('paymentTotal');
            const itemsList = document.getElementById('paymentItems');
            
            totalAmount.textContent = formatCurrency(amount);
            
            // Clear and populate items
            itemsList.innerHTML = '';
            items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex justify-between items-center py-2 border-b border-gray-200';
                itemDiv.innerHTML = `
                    <span class="text-sm text-gray-600">${item.name} x ${item.quantity}</span>
                    <span class="text-sm font-medium">${formatCurrency(item.price * item.quantity)}</span>
                `;
                itemsList.appendChild(itemDiv);
            });
            
            modal.classList.remove('hidden');
            modal.classList.add('animate-fade-in-up');
        }
        
        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('animate-fade-out');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('animate-fade-out', 'animate-fade-in-up');
            }, 300);
        }
        
        function formatCurrency(amount) {
            return '$' + parseFloat(amount).toFixed(2);
        }
        
        // Implement lazy loading for images
        function implementLazyLoading() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                img.classList.add('animate-fade-in-up');
                            }
                            observer.unobserve(img);
                        }
                    });
                });

                const lazyImages = document.querySelectorAll('img[data-src]');
                lazyImages.forEach(img => {
                    imageObserver.observe(img);
                });
            }
        }
    </script>
</body>
</html>