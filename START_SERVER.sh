#!/bin/bash
# SkillBridge Server Startup Script

echo "=== Starting SkillBridge Server ==="
echo ""
echo "Server will start at: http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

cd "$(dirname "$0")"
php -S localhost:8000

