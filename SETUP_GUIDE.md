# SkillBridge Setup Guide

This guide will help you set up SkillBridge on any system.

## Quick Setup (Automated)

### Option 1: Using Setup Scripts

#### For Linux/macOS:
```bash
chmod +x INSTALL.sh
./INSTALL.sh
```

#### For Windows:
```cmd
INSTALL.bat
```

### Option 2: Using PHP Setup Script
```bash
php setup.php
```

## Manual Setup

### Step 1: Prerequisites

Ensure you have:
- **PHP 7.4 or higher** (check with `php -v`)
- **MySQL 5.7 or higher** (check with `mysql --version`)
- **Web server** (Apache/Nginx) OR PHP built-in server

### Step 2: Configure Database

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
define('DB_NAME', 'skillbridge');
```

### Step 3: Run Setup Script

```bash
php setup.php
```

This will:
- ✅ Check PHP version and extensions
- ✅ Test database connection
- ✅ Create database
- ✅ Import schema
- ✅ Create upload directories
- ✅ Verify installation

### Step 4: Configure Base URL

Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost:8000/');
```

Update this to match your server URL.

### Step 5: Start Server

#### Using PHP Built-in Server:
```bash
php -S localhost:8000
```

#### Using Apache:
- Place project in `htdocs` or `www` directory
- Access via `http://localhost/skillbridge/`

#### Using Nginx:
- Configure virtual host
- Point document root to project directory

### Step 6: Access Application

1. Open browser: `http://localhost:8000`
2. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

## Troubleshooting

### Database Connection Failed

**macOS:**
```bash
brew services start mysql
```

**Linux:**
```bash
sudo systemctl start mysql
# or
sudo systemctl start mariadb
```

**Windows:**
- Open Services (services.msc)
- Find MySQL service
- Start it

### PHP Extensions Missing

Install required extensions:

**Ubuntu/Debian:**
```bash
sudo apt-get install php-mysqli php-mbstring
```

**macOS (Homebrew):**
```bash
brew install php
```

**Windows:**
- Edit `php.ini`
- Uncomment extension lines:
  - `extension=mysqli`
  - `extension=mbstring`

### Permission Issues

**Linux/macOS:**
```bash
chmod -R 755 uploads/
chmod -R 755 uploads/resumes/
chmod -R 755 uploads/profiles/
chmod -R 755 uploads/logos/
```

**Windows:**
- Right-click `uploads` folder
- Properties → Security
- Give "Modify" permission to web server user

### Port Already in Use

If port 8000 is busy:
```bash
php -S localhost:8080
```
Then update `BASE_URL` in `config/config.php`

## Verification Checklist

After setup, verify:

- [ ] PHP version >= 7.4
- [ ] MySQL service running
- [ ] Database `skillbridge` created
- [ ] All tables created (users, students, companies, internships, applications)
- [ ] Admin user exists
- [ ] Upload directories created and writable
- [ ] BASE_URL configured correctly
- [ ] Can access `http://localhost:8000`

## Default Credentials

**Admin:**
- Username: `admin`
- Password: `admin123`

⚠️ **Important:** Change admin password after first login!

## File Structure

```
skillbridge/
├── setup.php          # Main setup script
├── INSTALL.sh         # Linux/macOS installer
├── INSTALL.bat        # Windows installer
├── database.sql       # Database schema
├── config/
│   ├── config.php     # Main configuration
│   └── database.php   # Database configuration
└── ...
```

## Support

If you encounter issues:

1. Check error messages in setup output
2. Verify MySQL is running
3. Check file permissions
4. Review `INSTALLATION.md` for detailed steps
5. Check PHP and MySQL error logs

## Next Steps

After successful setup:

1. ✅ Change admin password
2. ✅ Test student registration
3. ✅ Test company registration
4. ✅ Post test internship
5. ✅ Test application flow

Enjoy using SkillBridge! 🎉

