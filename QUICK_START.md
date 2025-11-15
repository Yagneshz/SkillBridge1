# 🚀 SkillBridge - Quick Start Guide

## ✅ Setup Complete!

Your SkillBridge project is now running!

## 🌐 Access the Application

**URL:** http://localhost:8000

## 🔑 Default Login Credentials

### Admin Account
- **Username:** `admin`
- **Password:** `admin123`
- **URL:** http://localhost:8000/auth/login.php

## 📋 What's Running

✅ PHP Development Server: `http://localhost:8000`  
✅ MySQL Database: `skillbridge`  
✅ Database Tables: Created and populated  
✅ Admin User: Ready to use  

## 🎯 Quick Test Steps

1. **Open Browser:** Navigate to http://localhost:8000
2. **Login as Admin:**
   - Click "Login"
   - Username: `admin`
   - Password: `admin123`
3. **Test Student Registration:**
   - Logout
   - Click "Register"
   - Select "Student"
   - Fill in details and register
4. **Test Company Registration:**
   - Logout
   - Click "Register"
   - Select "Company"
   - Fill in details and register

## 🛠️ Server Management

### Start Server
```bash
cd "/Users/sb-mac5/Desktop/untitled folder"
php -S localhost:8000
```

Or use the script:
```bash
./START_SERVER.sh
```

### Stop Server
Press `Ctrl+C` in the terminal where server is running

### Restart MySQL (if needed)
```bash
brew services restart mysql
```

## 📁 Project Structure

- `index.php` - Home page
- `auth/` - Login/Register pages
- `admin/` - Admin panel
- `student/` - Student panel
- `company/` - Company panel
- `config/` - Configuration files
- `database.sql` - Database schema

## 🔍 Features to Test

### Student Features
- ✅ Register & Login
- ✅ Update Profile
- ✅ Browse Internships
- ✅ Apply for Internships
- ✅ View Applications Status
- ✅ View Companies

### Company Features
- ✅ Register & Login
- ✅ Update Company Profile
- ✅ Post Internships
- ✅ Edit/Delete Internships
- ✅ View Applications
- ✅ Accept/Reject Applications

### Admin Features
- ✅ View All Companies
- ✅ View All Students
- ✅ Manage User Status
- ✅ Delete Users
- ✅ View Statistics

## 🐛 Troubleshooting

### Database Connection Error
- Check MySQL is running: `brew services list`
- Verify credentials in `config/database.php`

### Page Not Loading
- Check PHP server is running
- Verify URL: http://localhost:8000
- Check browser console for errors

### File Upload Issues
- Ensure `uploads/` directory has write permissions
- Check PHP upload settings

## 📚 Documentation

- `README.md` - Full documentation
- `INSTALLATION.md` - Installation guide
- `DATABASE_SETUP.md` - Database setup details
- `PROJECT_SUMMARY.md` - Project overview

## 🎉 You're All Set!

The application is ready to use. Start exploring the features!

