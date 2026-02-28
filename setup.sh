#!/bin/bash
# RPi Server Live Checker - Virtual Environment Setup Script for Linux/macOS

echo "========================================"
echo "RPi Server Live Checker - Setup"
echo "========================================"
echo ""

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "❌ ERROR: Python 3 is not installed"
    echo "Please install Python 3.7+ using your package manager:"
    echo "  - Ubuntu/Debian: sudo apt install python3 python3-venv python3-pip"
    echo "  - macOS: brew install python3"
    echo "  - CentOS/RHEL: sudo yum install python3"
    exit 1
fi

echo "[1/4] ✅ Python found:"
python3 --version
echo ""

# Check if virtual environment already exists
if [ -d ".venv" ]; then
    echo "[2/4] ℹ️  Virtual environment already exists (.venv folder found)"
    echo "     Skipping creation..."
else
    echo "[2/4] 🔧 Creating virtual environment..."
    python3 -m venv .venv
    if [ $? -ne 0 ]; then
        echo "❌ ERROR: Failed to create virtual environment"
        echo "Try: sudo apt install python3-venv"
        exit 1
    fi
    echo "     ✅ Virtual environment created successfully!"
fi
echo ""

echo "[3/4] 🚀 Activating virtual environment..."
source .venv/bin/activate
echo ""

echo "[4/4] 📦 Installing required packages..."
pip install requests
if [ $? -ne 0 ]; then
    echo "❌ ERROR: Failed to install packages"
    exit 1
fi
echo ""

echo "========================================"
echo "✅ Setup Complete!"
echo "========================================"
echo ""
echo "Virtual environment is ready at: .venv/"
echo ""
echo "To activate it manually, run:"
echo "  source .venv/bin/activate"
echo ""
echo "Next steps:"
echo "1. Configure email alerts (optional):"
echo "   - Copy config.email.example.php to config.email.php"
echo "   - Edit with your Gmail App Password"
echo ""
echo "2. Configure SMS alerts (optional):"
echo "   - Copy config.sms.example.php to config.sms.php"
echo "   - Edit with your Twilio credentials"
echo ""
echo "3. Access the application:"
echo "   - Visit: http://localhost/rpi_server_live_checker/"
echo "   - Login: admin / admin"
echo ""
echo "See INSTALLATION.md for detailed instructions."
echo "========================================"
