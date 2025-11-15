# Database Setup Instructions

## Option 1: Using MySQL Command Line

1. **Start MySQL service** (if not running):
   ```bash
   # macOS with Homebrew
   brew services start mysql
   
   # Or start manually
   mysql.server start
   ```

2. **Create database and import schema**:
   ```bash
   mysql -u root -p < database.sql
   ```
   
   Or manually:
   ```bash
   mysql -u root -p
   ```
   Then in MySQL:
   ```sql
   CREATE DATABASE skillbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE skillbridge;
   SOURCE /Users/sb-mac5/Desktop/untitled\ folder/database.sql;
   ```

## Option 2: Using phpMyAdmin

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click "New" to create a database
3. Name it: `skillbridge`
4. Select it, then go to "Import" tab
5. Choose file: `database.sql`
6. Click "Go"

## Option 3: Using MySQL Workbench

1. Open MySQL Workbench
2. Connect to your MySQL server
3. Create a new schema named `skillbridge`
4. Open `database.sql` file
5. Execute all statements

## Verify Setup

After setup, verify:
```bash
mysql -u root -p -e "USE skillbridge; SELECT username, email, user_type FROM users;"
```

You should see the admin user:
- Username: `admin`
- Email: `admin@skillbridge.com`
- Password: `admin123`

## Update Database Credentials

If your MySQL has a password, update `config/database.php`:
```php
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
```

## Troubleshooting

### MySQL not running
```bash
# Check status
brew services list

# Start MySQL
brew services start mysql
```

### Connection refused
- Check MySQL is running: `mysql.server status`
- Verify credentials in `config/database.php`
- Check MySQL port (default: 3306)

### Permission denied
- Make sure MySQL user has CREATE DATABASE privileges
- Try: `mysql -u root -p` and grant privileges

