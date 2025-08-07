# EcoStore Database Setup Guide

## Prerequisites
- PostgreSQL installed and running
- Database management tool (pgAdmin, DBeaver, or command line)
- PHP with PDO PostgreSQL extension

## Step 1: Create Database

First, create the database using your SQL editor or command line:

```sql
CREATE DATABASE ecostore;
```

## Step 2: Connect to Database

Update your database configuration in `config/database.php`:

```php
$host = 'localhost';        // Your PostgreSQL host
$dbname = 'ecostore';      // Database name
$username = 'postgres';     // Your PostgreSQL username
$password = 'your_password'; // Your PostgreSQL password
$port = '5432';            // PostgreSQL port (default: 5432)
```

## Step 3: Test Connection

Run the database setup script to test your connection:

```bash
php database_setup.php
```

## Step 4: Execute Table Creation Scripts

Run each of the following SQL scripts in your SQL editor in the order provided below.