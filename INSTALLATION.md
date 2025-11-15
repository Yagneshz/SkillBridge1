# SkillBridge Installation Guide

## Step-by-Step Installation

### 1. Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### 2. Download/Clone Project
- Place the project folder in your web server directory
- For XAMPP: `C:\xampp\htdocs\skillbridge\`
- For WAMP: `C:\wamp\www\skillbridge\`
- For Linux: `/var/www/html/skillbridge/`

### 3. Database Setup

#### Option A: Using phpMyAdmin
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Create a new database named `skillbridge`
3. Import the `database.sql` file:
   - Click on the `skillbridge` database
   - Go to "Import" tab
   - Choose file `database.sql`
   - Click "Go"

#### Option B: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE skillbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillbridge;
SOURCE /path/to/database.sql;
```

### 4. Configuration

#### Update Database Credentials
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
define('DB_NAME', 'skillbridge');
```

#### Update Base URL
Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost/skillbridge/');
```
Change this to match your domain/server path.

### 5. File Permissions

Ensure upload directories have write permissions:

**Linux/Mac:**
```bash
chmod -R 755 uploads/
chmod -R 755 uploads/resumes/
chmod -R 755 uploads/profiles/
chmod -R 755 uploads/logos/
```

**Windows:**
- Right-click on `uploads` folder
- Properties → Security → Edit
- Give "Modify" permission to your web server user

### 6. Web Server Configuration

#### Apache (.htaccess already included)
- Ensure `mod_rewrite` is enabled
- Allow `.htaccess` files

#### Nginx Configuration
Add to your server block:
```nginx
location /skillbridge {
    try_files $uri $uri/ /skillbridge/index.php?$query_string;
}
```

### 7. Access the Application

1. Open your browser
2. Navigate to: `http://localhost/skillbridge/`
3. Default admin credentials:
   - Username: `admin`
   - Password: `admin123`

### 8. Testing

1. **Test Student Registration:**
   - Go to Register → Select "Student"
   - Create a test student account

2. **Test Company Registration:**
   - Go to Register → Select "Company"
   - Create a test company account

3. **Test Features:**
   - Login as student → Browse internships → Apply
   - Login as company → Post internship → View applications
   - Login as admin → Manage users

### 9. Troubleshooting

#### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database name is `skillbridge`

#### File Upload Not Working
- Check file permissions on `uploads/` directory
- Verify PHP `upload_max_filesize` and `post_max_size` settings
- Check `.htaccess` file exists

#### Page Not Found (404)
- Verify `BASE_URL` in `config/config.php` matches your setup
- Check Apache `mod_rewrite` is enabled
- Ensure `.htaccess` file is present

#### Session Issues
- Check PHP `session.save_path` is writable
- Verify cookies are enabled in browser

### 10. Security Recommendations

1. **Change Default Admin Password:**
   - Login as admin
   - Update password immediately

2. **Update Database Password:**
   - Use strong password for MySQL user
   - Update in `config/database.php`

3. **Production Settings:**
   - Disable error display in production
   - Use HTTPS
   - Regular database backups

### 11. Support

For issues or questions:
- Check `README.md` for feature documentation
- Review error logs in web server logs
- Check PHP error logs

## Quick Start Checklist

- [ ] Database created and imported
- [ ] Database credentials updated
- [ ] BASE_URL configured
- [ ] Upload directories have write permissions
- [ ] Web server configured
- [ ] Tested admin login
- [ ] Tested student registration
- [ ] Tested company registration

## Next Steps

After installation:
1. Change admin password
2. Customize branding/colors if needed
3. Add your logo/images
4. Configure email settings (if needed)
5. Set up regular backups

