@echo off
REM RPi Server Live Checker - Virtual Environment Setup Script for Windows
echo ========================================
echo RPi Server Live Checker - Setup
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python 3.7+ from https://www.python.org/downloads/
    echo Make sure to check "Add Python to PATH" during installation
    pause
    exit /b 1
)

echo [1/4] Python found:
python --version
echo.

REM Check if virtual environment already exists
if exist ".venv\" (
    echo [2/4] Virtual environment already exists (.venv folder found)
    echo      Skipping creation...
) else (
    echo [2/4] Creating virtual environment...
    python -m venv .venv
    if %errorlevel% neq 0 (
        echo ERROR: Failed to create virtual environment
        pause
        exit /b 1
    )
    echo      Virtual environment created successfully!
)
echo.

echo [3/4] Activating virtual environment...
call .venv\Scripts\activate.bat
echo.

echo [4/4] Installing required packages...
pip install requests
if %errorlevel% neq 0 (
    echo ERROR: Failed to install packages
    pause
    exit /b 1
)
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Virtual environment is ready at: .venv\
echo.
echo To activate it manually, run:
echo   .venv\Scripts\activate
echo.
echo Next steps:
echo 1. Configure email alerts (optional):
echo    - Copy config.email.example.php to config.email.php
echo    - Edit with your Gmail App Password
echo.
echo 2. Configure SMS alerts (optional):
echo    - Copy config.sms.example.php to config.sms.php
echo    - Edit with your Twilio credentials
echo.
echo 3. Access the application:
echo    - Visit: http://localhost/rpi_server_live_checker/
echo    - Login: admin / admin
echo.
echo See INSTALLATION.md for detailed instructions.
echo ========================================
pause
