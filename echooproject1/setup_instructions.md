# EcoStore Database Setup Instructions

## Quick Setup Guide

### 1. Install PostgreSQL
- Download and install PostgreSQL from https://www.postgresql.org/download/
- Remember your PostgreSQL username and password during installation

### 2. Create Database
Open your SQL editor (pgAdmin, DBeaver, or command line) and run:
```sql
CREATE DATABASE ecostore;
```

### 3. Update Database Configuration
Edit `config/database.php` and update these values:
```php
$password = 'your_actual_password';  // Replace with your PostgreSQL password
```

### 4. Run SQL Scripts
Execute the SQL scripts in this order in your SQL editor:

1. `sql_scripts/01_create_users_table.sql`
2. `sql_scripts/02_create_categories_table.sql`
3. `sql_scripts/03_create_products_table.sql`
4. `sql_scripts/04_create_orders_table.sql`
5. `sql_scripts/05_create_order_items_table.sql`
6. `sql_scripts/06_create_cart_table.sql`
7. `sql_scripts/07_create_badges_table.sql`
8. `sql_scripts/08_create_user_badges_table.sql`
9. `sql_scripts/09_create_wishlist_table.sql`
10. `sql_scripts/10_insert_sample_data.sql`

### 5. Test Connection
Run the connection test:
```bash
php database_connection_test.php
```

### 6. Verify Setup
Run the main setup script:
```bash
php database_setup.php
```

## Demo Accounts
After setup, you can use these demo accounts:
- **Admin**: username: `admin`, password: `admin123`
- **Seller**: username: `greenseller`, password: `seller123`
- **Buyer**: username: `ecobuyer`, password: `buyer123`

## Troubleshooting

### Connection Issues
1. Ensure PostgreSQL service is running
2. Check if the database name is correct (`ecostore`)
3. Verify username and password
4. Confirm PostgreSQL is listening on port 5432

### PHP Issues
1. Ensure PHP PDO PostgreSQL extension is installed
2. Check PHP error logs for detailed error messages

### Permission Issues
1. Make sure your PostgreSQL user has CREATE privileges
2. Verify the user can connect to the database

## Database Schema Overview

The EcoStore database includes these main tables:
- **users**: User accounts with roles (buyer, seller, admin)
- **categories**: Product categories
- **products**: Sustainable products with carbon savings data
- **orders**: Purchase orders
- **order_items**: Individual items in orders
- **cart**: Shopping cart storage
- **badges**: Achievement badges
- **user_badges**: User badge assignments
- **wishlist**: User wishlists

Each table includes appropriate indexes for optimal performance.