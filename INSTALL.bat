@echo off
REM SkillBridge Installation Script for Windows
REM This script automates the setup process

echo ╔══════════════════════════════════════════════════════════════╗
echo ║           SkillBridge Installation Script (Windows)          ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

REM Check if PHP is installed
echo 📋 Checking PHP installation...
php -v >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ PHP found
    php -v | findstr /C:"PHP"
) else (
    echo ❌ PHP is not installed or not in PATH
    echo Please install PHP 7.4 or higher and add it to PATH
    pause
    exit /b 1
)

echo.
echo 📋 Checking MySQL installation...
mysql --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ MySQL found
    mysql --version
) else (
    echo ⚠️  MySQL not found in PATH
    echo Please ensure MySQL is installed and accessible
)

echo.
echo 📋 Checking MySQL service status...
sc query MySQL >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ MySQL service check completed
) else (
    echo ⚠️  Could not check MySQL service status
    echo Please ensure MySQL service is running
)

echo.
echo 📋 Running PHP setup script...
echo.

php setup.php

if %errorlevel% equ 0 (
    echo.
    echo ✅ Installation completed!
    echo.
    echo To start the server, run:
    echo   php -S localhost:8000
    echo.
    echo Then open your browser to:
    echo   http://localhost:8000
    echo.
) else (
    echo.
    echo ❌ Installation encountered errors
    echo Please check the output above for details
)

pause

