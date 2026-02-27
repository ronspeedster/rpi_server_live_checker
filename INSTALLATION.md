# RPi Server Live Checker - Installation Guide

## 📋 Prerequisites

Before installing RPi Server Live Checker, ensure you have the following installed:

### Required Software

1. **Web Server with PHP**
   - Apache/Nginx with PHP 7.4 or higher
   - PHP Extensions required:
     - PDO (SQLite support)
     - cURL
     - OpenSSL
     - JSON
   - Recommended: MAMP, XAMPP, or WAMP for local development

2. **Python 3.7 or higher**
   - Windows: Download from [python.org](https://www.python.org/downloads/)
   - Linux/macOS: Usually pre-installed, or install via package manager
   - **Important**: Make sure to check "Add Python to PATH" during installation

3. **Database**
   - SQLite (included with PHP PDO)

4. **Email Service** (Optional - for email alerts)
   - Gmail account with App Password enabled
   - Or any SMTP server credentials

5. **Twilio Account** (Optional - for SMS alerts)
   - Free trial account at [twilio.com](https://www.twilio.com/try-twilio)
   - Provides $15 free credit (~267 SMS to Philippines)

---

## 🚀 Installation Steps

### 1. Clone or Download the Repository

```bash
# Clone the repository
git clone https://github.com/ronspeedster/rpi_server_live_checker.git
cd rpi_server_live_checker

# Or download and extract the ZIP file
```

### 2. Set Up Python Virtual Environment

**Why?** The monitoring script requires Python packages (like `requests` for Twilio SMS). A virtual environment keeps these dependencies isolated from your system Python.

#### Windows:

```cmd
# Navigate to the project directory
cd C:\MAMP\htdocs\rpi_server_live_checker

# Create virtual environment
python -m venv .venv

# Activate virtual environment
.venv\Scripts\activate

# Install required packages
pip install requests
```

#### Linux/macOS:

```bash
# Navigate to the project directory
cd /path/to/rpi_server_live_checker

# Create virtual environment
python3 -m venv .venv

# Activate virtual environment
source .venv/bin/activate

# Install required packages
pip install requests
```

**✅ Verify Installation:**

```bash
python -c "import requests; print('Requests library installed successfully!')"
```

### 3. Configure Web Server

Point your web server's document root to the project directory, or place the project in your web server's `htdocs`/`www` directory.

**Example for MAMP:**
- Place project in: `C:\MAMP\htdocs\rpi_server_live_checker\`
- Access via: `http://localhost/rpi_server_live_checker/`

### 4. Initialize the Database

Visit the application in your browser - the database will be created automatically on first access:

```
http://localhost/rpi_server_live_checker/
```

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin`
- ⚠️ **You will be forced to change this password on first login**

### 5. Configure Email Alerts (Optional)

1. **Copy the example config:**
   ```bash
   cp config.email.example.php config.email.php
   ```

2. **Edit `config.email.php`:**
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password'); // Gmail App Password
   define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
   define('SMTP_FROM_NAME', 'RPi Server Live Checker');
   ```

3. **For Gmail: Create an App Password**
   - Go to [Google Account Security](https://myaccount.google.com/security)
   - Enable 2-Step Verification (if not already enabled)
   - Go to [App Passwords](https://myaccount.google.com/apppasswords)
   - Generate a new App Password for "Mail"
   - Use the 16-character password in the config

4. **Test Email Configuration:**
   ```
   http://localhost/rpi_server_live_checker/test_email.php
   ```

### 6. Configure SMS Alerts (Optional)

1. **Sign up for Twilio:**
   - Create free account at [twilio.com/try-twilio](https://www.twilio.com/try-twilio)
   - Get your Account SID and Auth Token from the dashboard
   - Get a free phone number

2. **Enable Geographic Permissions:**
   - Go to [Geographic Permissions](https://console.twilio.com/us1/develop/sms/settings/geo-permissions)
   - Enable SMS for countries you want to send to (e.g., Philippines, Australia)
   - Check boxes: ✅ SMS, ✅ MMS, ✅ WhatsApp
   - Click "Save"

3. **Copy the example config:**
   ```bash
   cp config.sms.example.php config.sms.php
   ```

4. **Edit `config.sms.php`:**
   ```php
   define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
   define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
   define('TWILIO_FROM_NUMBER', '+15551234567'); // Your Twilio phone number
   ```

5. **Test SMS Configuration:**
   ```
   http://localhost/rpi_server_live_checker/test_sms.php
   ```

---

## 🔧 Configuration

### Add Devices to Monitor

1. Login to the dashboard
2. Go to **Devices** → **Add Device**
3. Fill in:
   - **Device Name**: Friendly name (e.g., "Office Server")
   - **IP Address**: IP address to monitor (e.g., `192.168.1.100`)
   - **Notes**: Optional description
   - **Enable Email Notifications**: Check if you want email alerts
   - **Enable SMS Notifications**: Check if you want SMS alerts
   - **Select Users**: Choose which users should receive alerts
4. Click **Save**

### Add Users

1. Go to **Users** → **Add User**
2. Fill in user information:
   - **Username** (required)
   - **Email** (required for email alerts)
   - **Phone** (required for SMS alerts)
     - Philippine format: `09171234567` or `+639171234567`
     - Australian format: `0412345678` or `+61412345678`
3. Click **Save**

---

## 🎯 Running the Monitor

### Option 1: Via Web Dashboard (Recommended)

1. Login to the dashboard
2. Go to **Monitor** → **Control**
3. Click **"Start Monitoring"**
4. Set ping interval (default: 60 seconds)
5. Click **Start**

The monitoring service will:
- Run continuously in the background
- Ping all active devices every X seconds
- Send alerts when devices are offline for 5+ minutes
- Re-alert every 30 minutes if device remains offline
- Auto-resolve alerts when device comes back online

### Option 2: Via Command Line

**Single Ping Check:**
```bash
# Activate virtual environment first
.venv\Scripts\activate

# Run single check
python scripts/ping_devices.py
```

**Continuous Monitoring:**
```bash
# Activate virtual environment first
.venv\Scripts\activate

# Run continuous monitoring with 60 second interval
python scripts/ping_devices.py --continuous --interval 60

# With logging
python scripts/ping_devices.py --continuous --interval 60 --log
```

**Stop Monitoring:**
- Press `Ctrl+C` in the terminal
- Or use the **Stop** button in the web dashboard

---

## 📊 Alert System

### How It Works

1. **Detection**: Device is pinged every X seconds (configurable)
2. **Offline Threshold**: After 5 minutes of continuous offline status
3. **First Alert**: Email/SMS sent to configured users
4. **Re-Alert**: If still offline after 30 minutes, send another alert
5. **Auto-Resolve**: When device comes back online, alert is automatically resolved

### Alert Types

- **Email**: HTML formatted email with device details
- **SMS**: Text message with device name, IP, and timestamp

### Supported Phone Number Formats

**Philippine Numbers:**
- `09171234567` → `+639171234567`
- `639171234567` → `+639171234567`
- `+639171234567` (already correct)

**Australian Numbers:**
- `0412345678` → `+61412345678`
- `61412345678` → `+61412345678`
- `+61412345678` (already correct)

---

## 🛠️ Troubleshooting

### Virtual Environment Issues

**Problem:** `ModuleNotFoundError: No module named 'requests'`

**Solution:**
```bash
# Make sure virtual environment is activated
.venv\Scripts\activate  # Windows
source .venv/bin/activate  # Linux/macOS

# Reinstall requests
pip install requests
```

### Monitoring Won't Start from Dashboard

**Problem:** "The process may have started but stopped immediately"

**Solution:**
1. Check that virtual environment exists: `.venv` folder in project root
2. Verify Python packages installed: `pip list` (should show `requests`)
3. Check log file: `data/monitor.log` for error details
4. Ensure data directory is writable

### Email Not Sending

**Problem:** Email alerts not received

**Solution:**
1. Test email config at: `test_email.php`
2. Verify Gmail App Password is correct (16 characters, no spaces)
3. Check that 2-Step Verification is enabled on Gmail
4. Look for error messages in the test page

### SMS Not Sending

**Problem:** SMS alerts not received

**Solution:**
1. Test SMS config at: `test_sms.php`
2. Verify Twilio credentials are correct
3. **Check Geographic Permissions** - most common issue!
   - Go to [Geo Permissions](https://console.twilio.com/us1/develop/sms/settings/geo-permissions)
   - Enable the country you're sending to
4. Check Twilio account balance ($15 on trial)
5. Verify phone number format is correct

### Database Errors

**Problem:** Database not found or permission errors

**Solution:**
1. Ensure `data` directory exists and is writable
2. Delete `data/network_monitor.sqlite` and refresh the page to recreate
3. Check PHP has PDO SQLite extension enabled

---

## 📁 Project Structure

```
rpi_server_live_checker/
├── .venv/                      # Python virtual environment (you create this)
├── config.php                  # Main configuration
├── config.email.php            # Email configuration (copy from example)
├── config.sms.php             # SMS configuration (copy from example)
├── index.php                   # Entry point
├── login.php                   # Login page
├── dashboard.php               # Main dashboard
├── css/                        # Stylesheets
├── data/                       # Database and logs
│   ├── network_monitor.sqlite  # SQLite database
│   └── monitor.log            # Monitoring logs
├── devices/                    # Device management
├── users/                      # User management
├── monitor/                    # Monitoring controls
├── includes/                   # Helper classes
│   ├── EmailHelper.php        # Email functionality
│   └── SMSHelper.php          # SMS functionality
├── scripts/                    # Python monitoring scripts
│   └── ping_devices.py        # Main monitoring script
└── INSTALLATION.md            # This file
```

---

## 🔐 Security Notes

1. **Change default admin password immediately** after first login
2. **Never commit config files with credentials** to version control
   - `config.email.php` and `config.sms.php` are in `.gitignore`
3. **Use App Passwords** for Gmail, not your main password
4. **Secure your Twilio credentials** - treat them like passwords
5. **Use HTTPS in production** to encrypt credentials in transit
6. **Regularly update** dependencies and PHP version

---

## 📝 License

See LICENSE file for details.

---

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review log files in `data/monitor.log`
3. Create an issue on GitHub

---

**Happy Monitoring! 🚀**
