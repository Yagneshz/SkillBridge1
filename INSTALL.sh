#!/bin/bash

# SkillBridge Installation Script for Linux/macOS
# This script automates the setup process

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║           SkillBridge Installation Script                    ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if PHP is installed
echo "📋 Checking PHP installation..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2)
    echo -e "${GREEN}✅ PHP found: $PHP_VERSION${NC}"
else
    echo -e "${RED}❌ PHP is not installed${NC}"
    echo "Please install PHP 7.4 or higher"
    exit 1
fi

# Check if MySQL is installed
echo "📋 Checking MySQL installation..."
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✅ MySQL found${NC}"
else
    echo -e "${YELLOW}⚠️  MySQL not found in PATH${NC}"
    echo "Please ensure MySQL is installed and accessible"
fi

# Check if MySQL is running
echo "📋 Checking MySQL service status..."
if pgrep -x "mysqld" > /dev/null || pgrep -x "mysql" > /dev/null; then
    echo -e "${GREEN}✅ MySQL service appears to be running${NC}"
else
    echo -e "${YELLOW}⚠️  MySQL service may not be running${NC}"
    echo "Attempting to start MySQL..."
    
    # Try to start MySQL (different commands for different systems)
    if command -v brew &> /dev/null; then
        brew services start mysql 2>/dev/null || mysql.server start 2>/dev/null
    elif command -v systemctl &> /dev/null; then
        sudo systemctl start mysql 2>/dev/null || sudo systemctl start mariadb 2>/dev/null
    elif command -v service &> /dev/null; then
        sudo service mysql start 2>/dev/null || sudo service mariadb start 2>/dev/null
    fi
    
    sleep 2
fi

# Run PHP setup script
echo ""
echo "📋 Running PHP setup script..."
echo ""

php setup.php

# Check exit status
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ Installation completed!${NC}"
    echo ""
    echo "To start the server, run:"
    echo "  php -S localhost:8000"
    echo ""
    echo "Then open your browser to:"
    echo "  http://localhost:8000"
    echo ""
else
    echo ""
    echo -e "${RED}❌ Installation encountered errors${NC}"
    echo "Please check the output above for details"
    exit 1
fi

