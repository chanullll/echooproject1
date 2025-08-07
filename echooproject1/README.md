# EcoStore - Eco-Friendly E-commerce Platform

A complete responsive eco-friendly e-commerce website built with pure PHP, PostgreSQL, and Tailwind CSS.

## 🌱 Features

### Core Functionality
- **User Authentication**: Session-based login/register system with role-based access (buyer, seller, admin)
- **Product Management**: Full CRUD operations for sustainable products
- **Shopping Cart**: Persistent cart with checkout simulation
- **Order Management**: Complete order processing and history
- **Eco Badges**: Gamified achievement system based on CO₂ saved
- **Leaderboard**: Community ranking by environmental impact

### Role-Based Dashboards
- **Buyer Dashboard**: Orders, carbon saved, badges, progress tracking
- **Seller Dashboard**: Product management, sales analytics, impact stats
- **Admin Dashboard**: User management, product approvals, platform analytics

### Environmental Impact
- **Carbon Tracking**: Each product shows CO₂ savings compared to conventional alternatives
- **Impact Visualization**: Real-time display of environmental benefits
- **Badge System**: Recognition for eco-friendly achievements
- **Community Leaderboard**: Competitive element to encourage sustainable shopping

## 🛠️ Tech Stack

- **Backend**: Pure PHP (no frameworks)
- **Database**: PostgreSQL
- **Frontend**: HTML5, Tailwind CSS (via CDN)
- **JavaScript**: Vanilla JS for interactivity
- **Server**: XAMPP (Apache + PHP)

## 📁 Project Structure

```
ecostore/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── functions.php         # Common utility functions
│   ├── header.php           # Site header and navigation
│   └── footer.php           # Site footer
├── dashboard/
│   ├── buyer.php            # Buyer dashboard content
│   ├── seller.php           # Seller dashboard content
│   └── admin.php            # Admin dashboard content
├── database/
│   └── schema.sql           # PostgreSQL database schema
├── index.php                # Homepage
├── login.php                # User login
├── register.php             # User registration
├── logout.php               # Session logout
├── products.php             # Product listing with filters
├── product.php              # Individual product details
├── cart.php                 # Shopping cart and checkout
├── dashboard.php            # Role-based dashboard router
├── leaderboard.php          # Eco-friendly user rankings
└── README.md               # This file
```

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache + PHP + PostgreSQL)
- PostgreSQL database server
- Web browser

### Database Setup
1. Create PostgreSQL database:
   ```sql
   CREATE DATABASE ecostore;
   ```

2. Import the schema:
   ```bash
   psql -U postgres -d ecostore -f database/schema.sql
   ```

3. Update database credentials in `config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'ecostore';
   $username = 'postgres';
   $password = 'your_password';
   ```

### XAMPP Configuration
1. Start Apache and PostgreSQL services
2. Place project files in `htdocs/ecostore/`
3. Access via `http://localhost/ecostore/`

## 👥 Demo Accounts

The database includes pre-configured demo accounts:

- **Admin**: `admin` / `admin123`
- **Seller**: `greenseller` / `seller123`
- **Buyer**: `ecobuyer` / `buyer123`

## 🌍 Key Features Explained

### Environmental Impact Tracking
- Each product displays CO₂ savings compared to conventional alternatives
- Users accumulate carbon savings with each purchase
- Real-time impact visualization throughout the platform

### Gamification System
- **Badges**: Earned based on total CO₂ saved
  - Green Beginner (5kg CO₂)
  - Eco Warrior (25kg CO₂)
  - Planet Protector (50kg CO₂)
  - Climate Champion (100kg CO₂)
  - Earth Guardian (250kg CO₂)

### Role-Based Access Control
- **Buyers**: Browse, purchase, track impact, earn badges
- **Sellers**: List products, manage inventory, view sales analytics
- **Admins**: Approve products, manage users, view platform analytics

### Responsive Design
- Mobile-first approach with Tailwind CSS
- Optimized for all screen sizes
- Modern, clean interface with eco-friendly color scheme

## 🔧 Customization

### Adding New Product Categories
1. Insert into `categories` table
2. Update category filters in `products.php`

### Modifying Badge System
1. Update `badges` table with new thresholds
2. Modify `checkAndAwardBadges()` function in `includes/functions.php`

### Styling Changes
- Modify Tailwind classes throughout the templates
- Update color scheme in `includes/header.php` Tailwind config

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input sanitization and validation
- Session-based authentication
- CSRF token protection (framework included)
- Role-based access control

## 📊 Database Schema

### Core Tables
- `users`: User accounts with roles and carbon tracking
- `products`: Sustainable products with environmental impact data
- `orders`: Purchase history and carbon savings
- `cart`: Persistent shopping cart storage
- `badges`: Achievement system definitions
- `categories`: Product categorization

### Relationships
- Users can have multiple orders and badges
- Products belong to categories and sellers
- Orders contain multiple order items
- Cart items link users to products

## 🌟 Future Enhancements

- Payment gateway integration (Stripe/PayPal)
- Product reviews and ratings
- Wishlist functionality
- Email notifications
- Advanced analytics dashboard
- Mobile app API
- Social sharing features
- Seller verification system

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For questions or support, please open an issue on the project repository.

---

**EcoStore** - Making sustainable shopping accessible and rewarding for everyone! 🌱