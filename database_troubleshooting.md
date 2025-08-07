# Database Connection Troubleshooting Guide

## Error Analysis
The error `Connection refused (0x0000274D/10061)` means PostgreSQL is either:
1. Not installed
2. Not running
3. Running on a different port
4. Not configured to accept connections

## Solution Steps

### Step 1: Check if PostgreSQL is Installed and Running

#### On Windows:
1. **Check if PostgreSQL is installed:**
   - Look for "PostgreSQL" in your Start menu
   - Or check `C:\Program Files\PostgreSQL\` folder

2. **Start PostgreSQL Service:**
   - Press `Win + R`, type `services.msc`
   - Look for "postgresql-x64-XX" service
   - Right-click → Start (if not running)

3. **Alternative - Use pgAdmin:**
   - Open pgAdmin (if installed)
   - Try to connect to your local server

#### On Mac:
```bash
# Check if PostgreSQL is running
brew services list | grep postgresql

# Start PostgreSQL
brew services start postgresql

# Or if installed via installer:
sudo launchctl load /Library/LaunchDaemons/com.edb.launchd.postgresql-XX.plist
```

#### On Linux:
```bash
# Check status
sudo systemctl status postgresql

# Start PostgreSQL
sudo systemctl start postgresql

# Enable auto-start
sudo systemctl enable postgresql
```

### Step 2: Install PostgreSQL (if not installed)

#### Windows:
1. Download from: https://www.postgresql.org/download/windows/
2. Run the installer
3. Remember the password you set for the `postgres` user
4. Default port is 5432

#### Mac:
```bash
# Using Homebrew
brew install postgresql
brew services start postgresql

# Or download installer from postgresql.org
```

#### Linux (Ubuntu/Debian):
```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### Step 3: Test Connection

#### Using Command Line:
```bash
# Test if PostgreSQL is listening
psql -U postgres -h localhost -p 5432

# If it asks for password, enter the one you set during installation
```

#### Using PHP (create test file):
```php
<?php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=postgres", "postgres", "your_password");
    echo "✅ PostgreSQL connection successful!";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
```

### Step 4: Create EcoStore Database

Once PostgreSQL is running:

```sql
-- Connect to PostgreSQL and run:
CREATE DATABASE echostore;
```

### Step 5: Update Configuration

Update your database password in the config file.

## Common Issues and Solutions

### Issue 1: Wrong Password
- Reset postgres user password:
```bash
sudo -u postgres psql
ALTER USER postgres PASSWORD 'newpassword';
```

### Issue 2: Wrong Port
- Check what port PostgreSQL is running on:
```bash
sudo netstat -plunt | grep postgres
```

### Issue 3: PostgreSQL Not Accepting Connections
- Edit `postgresql.conf`:
```
listen_addresses = 'localhost'
port = 5432
```

- Edit `pg_hba.conf`:
```
local   all             postgres                                md5
host    all             all             127.0.0.1/32            md5
```

### Issue 4: Firewall Blocking
- Windows: Allow PostgreSQL through Windows Firewall
- Linux: `sudo ufw allow 5432`

## Quick Fix for XAMPP Users

If you're using XAMPP, it comes with MySQL, not PostgreSQL. You have two options:

### Option A: Install PostgreSQL alongside XAMPP
1. Download and install PostgreSQL separately
2. Use PostgreSQL for this project

### Option B: Switch to MySQL (requires code changes)
1. Use MySQL instead of PostgreSQL
2. Update database configuration and SQL syntax

## Next Steps After Fixing

1. Ensure PostgreSQL is running
2. Update your password in `config/database.php`
3. Run the database setup scripts
4. Test the connection

## Need Help?

If you're still having issues:
1. Check which operating system you're using
2. Verify if you have XAMPP, WAMP, or other local server setup
3. Confirm if you want to use PostgreSQL or switch to MySQL