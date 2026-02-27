# RPi Server Live Checker

A lightweight, self-hosted network device monitoring system with email and SMS alerts. Built with PHP and Python for easy deployment on any web server.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![Python](https://img.shields.io/badge/Python-3.7%2B-blue)

## ✨ Features

- 🔍 **Real-time Device Monitoring** - Ping devices at configurable intervals
- 📧 **Email Alerts** - Receive email notifications via Gmail or any SMTP server
- 📱 **SMS Alerts** - Get instant SMS notifications via Twilio
- ⏰ **Smart Alerting** - 5-minute offline detection, 30-minute re-alerts
- 🔄 **Auto-Resolution** - Alerts automatically close when device comes back online
- 👥 **Multi-User Support** - Different users can monitor different devices
- 📊 **Ping History** - View historical uptime and response times
- 🌐 **Web Dashboard** - Clean, responsive interface to manage everything
- 🚀 **Easy Deployment** - No complex dependencies, runs on MAMP/XAMPP/WAMP
- 💾 **SQLite Database** - No MySQL required!

## 🎯 Use Cases

- Monitor Raspberry Pi servers and ensure they stay online
- Track network device availability (routers, switches, NAS)
- Get instant alerts when critical services go down
- Monitor remote sites or branch offices
- Keep tabs on IoT devices and smart home servers

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ with SQLite
- **Frontend**: HTML, CSS (SB Admin theme), JavaScript
- **Monitoring**: Python 3.7+ with ping capability
- **Email**: SMTP (Gmail, or any SMTP server)
- **SMS**: Twilio API
- **Database**: SQLite (no MySQL required!)

---

## 📋 Prerequisites

Before installing, ensure you have:

### Required Software

1. **Web Server with PHP 7.4+**
   - Apache/Nginx with PHP support
   - PHP Extensions: PDO (SQLite), cURL, OpenSSL, JSON
   - Recommended: MAMP, XAMPP, or WAMP for local development

2. **Python 3.7 or higher**
   - Windows: Download from [python.org](https://www.python.org/downloads/)
   - Linux/macOS: Usually pre-installed
   - ⚠️ **Important**: Check "Add Python to PATH" during installation

3. **Git** (optional, for cloning repository)

### Optional Services

4. **Gmail Account** (for email alerts)
   - With App Password enabled (2-Step Verification required)

5. **Twilio Account** (for SMS alerts)
   - Free trial at [twilio.com](https://www.twilio.com/try-twilio)
   - Provides $15 free credit (~267 SMS to Philippines)

---

## 🚀 Installation

### Option 1: Automated Setup (Recommended)

**Windows:**
```cmd
# 1. Download or clone the repository
git clone https://github.com/ronspeedster/rpi_server_live_checker.git
cd rpi_server_live_checker

# 2. Run automated setup
setup.bat
```

**Linux/macOS:**
```bash
# 1. Download or clone the repository
git clone https://github.com/ronspeedster/rpi_server_live_checker.git
cd rpi_server_live_checker

# 2. Make setup script executable and run
chmod +x setup.sh
./setup.sh
```

The setup script will:
- ✅ Check if Python is installed
- ✅ Create virtual environment (`.venv`)
- ✅ Install required packages (`requests`)
- ✅ Display next steps

### Option 2: Manual Setup

**Windows:**
```cmd
# 1. Clone repository
git clone https://github.com/ronspeedster/rpi_server_live_checker.git
cd rpi_server_live_checker

# 2. Create virtual environment
python -m venv .venv

# 3. Activate virtual environment
.venv\Scripts\activate

# 4. Install dependencies
pip install requests

# 5. Verify installation
python -c "import requests; print('✅ Setup complete!')"
```

**Linux/macOS:**
```bash
# 1. Clone repository
git clone https://github.com/ronspeedster/rpi_server_live_checker.git
cd rpi_server_live_checker

# 2. Create virtual environment
python3 -m venv .venv

# 3. Activate virtual environment
source .venv/bin/activate

# 4. Install dependencies
pip install requests

# 5. Verify installation
python -c "import requests; print('✅ Setup complete!')"
```

### Access the Application

1. **Place project in web server directory**
   - MAMP: `C:\MAMP\htdocs\rpi_server_live_checker\`
   - XAMPP: `C:\xampp\htdocs\rpi_server_live_checker\`
   - Linux: `/var/www/html/rpi_server_live_checker/`

2. **Visit in browser:**
   ```
   http://localhost/rpi_server_live_checker/
   ```

3. **Login with default credentials:**
   - Username: `admin`
   - Password: `admin`
   - ⚠️ You'll be forced to change this immediately

---

## ⚙️ Configuration

### 1. Email Alerts (Optional but Recommended)

**Step 1: Create Gmail App Password**

1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable **2-Step Verification** (if not already enabled)
3. Go to [App Passwords](https://myaccount.google.com/apppasswords)
4. Generate a new App Password for "Mail"
5. Copy the 16-character password

**Step 2: Configure Email**

Create `config.email.php`:
```php
<?php
// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // 16-character App Password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'RPi Server Live Checker');
?>
```

**Step 3: Test Email**

Visit: `http://localhost/rpi_server_live_checker/test_email.php`
- Click **"Send Test Email"**
- Check your inbox
- If successful, you're ready! ✅

### 2. SMS Alerts (Optional)

**Step 1: Create Twilio Account**

1. Sign up at [twilio.com/try-twilio](https://www.twilio.com/try-twilio)
2. Get free $15 credit (~267 SMS to Philippines or ~200 to Australia)
3. From Console Dashboard, copy:
   - **Account SID** (starts with `AC`)
   - **Auth Token**

**Step 2: Get a Phone Number**

1. In Twilio Console, go to **Phone Numbers** → **Manage** → **Buy a number**
2. Search for a number with SMS capability
3. Purchase (FREE with trial credits)
4. Copy your Twilio number (e.g., `+15551234567`)

**Step 3: Enable Geographic Permissions** ⚠️ CRITICAL!

This is the **#1 reason SMS fails** on trial accounts:

1. Go to [Geographic Permissions](https://console.twilio.com/us1/develop/sms/settings/geo-permissions)
2. Find countries you want to send to (e.g., **Philippines**, **Australia**)
3. For each country, check: ✅ **SMS**, ✅ **MMS**, ✅ **WhatsApp**
4. Click **"Save"**

Without this step, all SMS will fail with "Permission denied" error!

**Step 4: Configure SMS**

Create `config.sms.php`:
```php
<?php
// Twilio Configuration
define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN', 'your_auth_token_here');
define('TWILIO_FROM_NUMBER', '+15551234567'); // Your Twilio phone number
?>
```

**Step 5: Test SMS**

Visit: `http://localhost/rpi_server_live_checker/test_sms.php`
- Click **"Check Balance"** to verify connection
- Enter phone number (supports Philippine and Australian formats)
- Click **"Send Test SMS"**
- Check your phone! 📱

**Supported Phone Formats:**

| Country | Input Format | Converted To |
|---------|-------------|--------------|
| Philippines | `09171234567` | `+639171234567` |
| Philippines | `639171234567` | `+639171234567` |
| Philippines | `+639171234567` | `+639171234567` |
| Australia | `0412345678` | `+61412345678` |
| Australia | `61412345678` | `+61412345678` |
| Australia | `+61412345678` | `+61412345678` |

### 3. Add Users

1. Login to dashboard
2. Go to **Users** → **Add User**
3. Fill in:
   - **Username** (required)
   - **Email** (required for email alerts)
   - **Phone** (required for SMS alerts)
4. Click **Save**

### 4. Add Devices to Monitor

1. Go to **Devices** → **Add Device**
2. Configure device:
   - **Name**: Friendly name (e.g., "Office Server")
   - **IP Address**: IP to monitor (e.g., `192.168.1.100`)
   - **Notes**: Optional description
   - ✅ **Enable Email Notifications**
   - ✅ **Enable SMS Notifications**
   - **Select users** to receive alerts
3. Click **Save**

### 5. Start Monitoring

1. Go to **Monitor** → **Control**
2. Click **"Start Monitoring"**
3. Set **Ping Interval** (default: 60 seconds)
4. Click **Start**

The service will run in the background and send alerts when devices go offline!

---

## 🚦 How the Alert System Works

### Detection Flow

```
1. PING → Device every X seconds (configurable)
2. OFFLINE → 5 consecutive minutes of failed pings
3. ALERT → Email + SMS sent to configured users
4. RE-ALERT → Every 30 minutes if still offline
5. RESOLUTION → Auto-closes when device returns online
```

### Alert Timeline Example

- **0:00** - Device goes offline
- **0:01** - First failed ping detected
- **0:02** - Second failed ping
- **0:03** - Third failed ping
- **0:04** - Fourth failed ping
- **0:05** - Fifth failed ping → **ALERT TRIGGERED** 📧📱
- **0:35** - Still offline → **RE-ALERT** 📧📱
- **1:05** - Still offline → **RE-ALERT** 📧📱
- **1:20** - Device back online → **ALERT RESOLVED** ✅

### Email Alert Format

```
Subject: ⚠️ Device Alert: Office Server is OFFLINE

Device: Office Server
IP: 192.168.1.100
Status: OFFLINE
Time: 2026-02-27 14:30:00

This is an automated message from RPi Server Live Checker.
```

### SMS Alert Format

```
Sent from your Twilio trial account - ALERT: Office Server (192.168.1.100) is OFFLINE at Feb 27, 02:30 PM - RPi Server Live Checker
```

Note: Trial account prefix removed when you upgrade to paid (even with $0 balance).

---

## 🎮 Usage Guide

### Web Dashboard

**Monitor → Control**
- Start/stop monitoring service
- Set ping interval
- View service status

**Monitor → History**
- View ping logs for all devices
- Filter by device, date, status
- Export data

**Monitor → Logs**
- View live monitoring logs
- Real-time updates
- See alert triggers

**Devices**
- Add, edit, delete devices
- Enable/disable notifications
- Configure per-device alert settings

**Users**
- Manage user accounts
- Set email and phone numbers
- Assign alert recipients

### Command Line Usage

**Single Ping Check:**
```bash
# Activate virtual environment first
.venv\Scripts\activate  # Windows
# source .venv/bin/activate  # Linux/macOS

# Run single check
python scripts/ping_devices.py
```

**Continuous Monitoring:**
```bash
# Activate virtual environment first
.venv\Scripts\activate

# Run with 60 second interval
python scripts/ping_devices.py --continuous --interval 60

# With logging to file
python scripts/ping_devices.py --continuous --interval 60 --log
```

**Check Log File:**
```bash
# Windows
type data\monitor.log

# Linux/macOS
tail -f data/monitor.log
```

**Stop Monitoring:**
- Press `Ctrl+C` in terminal
- Or use **Stop** button in web dashboard

---

## 🛠️ Troubleshooting

### Virtual Environment Issues

**Problem:** `ModuleNotFoundError: No module named 'requests'`

**Solution:**
```bash
# Make sure virtual environment is activated
.venv\Scripts\activate  # Windows
# source .venv/bin/activate  # Linux/macOS

# Reinstall requests
pip install requests

# Verify
python -c "import requests; print('OK')"
```

### Monitoring Won't Start from Dashboard

**Problem:** "The process may have started but stopped immediately"

**Causes & Solutions:**

1. **Virtual environment missing**
   ```bash
   # Check if .venv folder exists
   ls .venv  # Should exist
   
   # If not, create it
   python -m venv .venv
   .venv\Scripts\activate
   pip install requests
   ```

2. **Requests library not installed**
   ```bash
   .venv\Scripts\python -c "import requests"
   # If error, run: pip install requests
   ```

3. **Database not initialized**
   - Visit the application in browser first
   - Database will be created automatically

4. **Check log file for errors**
   ```bash
   cat data/monitor.log  # Linux/macOS
   type data\monitor.log  # Windows
   ```

### Email Not Sending

**Problem:** Email alerts not received

**Checklist:**

1. ✅ Test at `test_email.php` first
2. ✅ Verify Gmail App Password (16 characters, no spaces)
3. ✅ Check that 2-Step Verification is enabled on Gmail
4. ✅ If using other SMTP, verify host/port/credentials
5. ✅ Check spam folder
6. ✅ Review error messages in test page

**Common Errors:**

- `Authentication failed` → Wrong password or username
- `Could not connect` → Wrong SMTP host or port
- `Timed out` → Firewall blocking port 587

### SMS Not Sending

**Problem:** SMS alerts not received

**Checklist:**

1. ✅ Test at `test_sms.php` first
2. ✅ Verify Twilio credentials are correct
3. ✅ **Check Geographic Permissions** ← Most common issue!
4. ✅ Verify phone number format
5. ✅ Check Twilio balance ($15 on trial)
6. ✅ Review error codes in test page

**Geographic Permissions** (CRITICAL):
1. Go to [Twilio Geo Permissions](https://console.twilio.com/us1/develop/sms/settings/geo-permissions)
2. Find your country (Philippines, Australia, etc.)
3. Check ✅ SMS, ✅ MMS, ✅ WhatsApp
4. Click **Save**
5. Try sending SMS again

**Common Twilio Error Codes:**

- `21211` - Invalid "To" phone number format
- `21212` - Invalid "From" phone number (check config.sms.php)
- `21606` - Phone number not verified (trial account)
- `21408` - Permission to send has not been enabled (geographic permissions!)

### Database Errors

**Problem:** Database not found or permission errors

**Solutions:**

1. **Ensure data directory exists and is writable**
   ```bash
   mkdir data  # Create if missing
   chmod 777 data  # Linux/macOS - make writable
   ```

2. **Delete and recreate database**
   ```bash
   rm data/network_monitor.sqlite
   # Then visit the application in browser - will recreate automatically
   ```

3. **Check PHP has PDO SQLite extension**
   ```bash
   php -m | grep pdo_sqlite  # Should show "pdo_sqlite"
   ```

---

## 📁 Project Structure

```
rpi_server_live_checker/
├── .venv/                         # Python virtual environment (you create this)
├── .gitignore                     # Excludes config files from Git
├── setup.bat                      # Windows setup script
├── setup.sh                       # Linux/macOS setup script
├── README.md                      # This file - complete documentation
│
├── config.php                     # Main PHP configuration
├── config.email.php               # Email configuration (create from example)
├── config.sms.php                 # SMS configuration (create from example)
│
├── index.php                      # Application entry point
├── login.php                      # Login page
├── logout.php                     # Logout handler
├── dashboard.php                  # Main dashboard
├── change_password.php            # Password change page
├── init_db.php                    # Database initialization
│
├── test_email.php                 # Email configuration tester
├── test_sms.php                   # SMS configuration tester
│
├── css/                           # Stylesheets
│   ├── sb-admin-2.css
│   └── sb-admin-2.min.css
│
├── data/                          # Database and logs
│   ├── network_monitor.sqlite     # SQLite database  (auto-created)
│   ├── monitor.log                # Monitoring logs (auto-created)
│   └── monitor.pid                # Process ID file (auto-created)
│
├── devices/                       # Device management
│   ├── index.php                  # Device list
│   ├── add.php                    # Add device
│   ├── edit.php                   # Edit device
│   └── delete.php                 # Delete device
│
├── users/                         # User management
│   ├── index.php                  # User list
│   ├── add.php                    # Add user
│   ├── edit.php                   # Edit user
│   └── delete.php                 # Delete user
│
├── monitor/                       # Monitoring controls
│   ├── control.php                # Start/stop monitoring
│   ├── history.php                # Ping history
│   ├── logs.php                   # Live logs viewer
│   └── stream_logs.php            # Log streaming endpoint
│
├── includes/                      # Helper classes and includes
│   ├── header.php                 # Page header
│   ├── footer.php                 # Page footer
│   ├── sidebar.php                # Navigation sidebar
│   ├── topbar.php                 # Top navigation bar
│   ├── alerts.php                 # Alert messages
│   ├── EmailHelper.php            # Email functionality
│   └── SMSHelper.php              # SMS functionality
│
└── scripts/                       # Python monitoring scripts
    ├── ping_devices.py            # Main monitoring script
    └── README.md                  # Script documentation
```

---

## 💡 Advanced Topics

### Customizing AlertMessages

**Email Alerts** - Edit `includes/EmailHelper.php`:
```php
// Line ~190: Customize email HTML template
$html = "
    <div class='header'>
        <h2 style='margin: 0;'>$icon Custom Alert Title</h2>
    </div>
    ...
";
```

**SMS Alerts** - Edit `includes/SMSHelper.php`:
```php
// Line ~260: Customize SMS message
public static function createAlertSMS($deviceName, $ipAddress, $status)
{
    $timestamp = date('M d, h:i A');
    return "CUSTOM ALERT: $deviceName at $ipAddress is $status - $timestamp";
}
```

**Python Script** - Edit `scripts/ping_devices.py`:
```python
# Line ~270: Customize Python SMS message
message = f"Your Custom Message: {device_name} ({device_ip}) - {alert_type}"
```

### Adding More Phone Number Formats

Edit both files to add support for your country:

1. **`includes/SMSHelper.php`** (Line ~115):
```php
// ===== YOUR COUNTRY =====
// 0XXXXXXXXX -> +COUNTRYCODE
if (preg_match('/^0(XXXXXXXXX)$/', $phone, $matches)) {
    return '+COUNTRYCODE' . $matches[1];
}
```

2. **`scripts/ping_devices.py`** (Line ~235):
```python
# ===== YOUR COUNTRY =====
elif phone.startswith('0X') and len(phone) == YY:
    return '+COUNTRYCODE' + phone[1:]
```

### Running as a System Service

**Linux (systemd):**

Create `/etc/systemd/system/rpi-monitor.service`:
```ini
[Unit]
Description=RPi Server Live Checker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/html/rpi_server_live_checker
ExecStart=/var/www/html/rpi_server_live_checker/.venv/bin/python /var/www/html/rpi_server_live_checker/scripts/ping_devices.py --continuous --interval 60 --log
Restart=always

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable rpi-monitor
sudo systemctl start rpi-monitor
sudo systemctl status rpi-monitor
```

**Windows (Task Scheduler):**

1. Open Task Scheduler
2. Create Basic Task
3. Trigger: At startup
4. Action: Start a program
5. Program: `C:\MAMP\htdocs\rpi_server_live_checker\.venv\Scripts\python.exe`
6. Arguments: `C:\MAMP\htdocs\rpi_server_live_checker\scripts\ping_devices.py --continuous --interval 60 --log`
7. Finish

---

## 🔐 Security Best Practices

1. **Change Default Password Immediately**
   - Default `admin/admin` is for initial setup only
   - Change on first login (forced)

2. **Protect Configuration Files**
   - Never commit `config.email.php` or `config.sms.php` to Git
   - Already in `.gitignore`
   - Set file permissions: `chmod 600 config.*.php` (Linux)

3. **Use HTTPS in Production**
   - Get free SSL with [Let's Encrypt](https://letsencrypt.org/)
   - Redirects login credentials and API tokens encrypted

4. **Secure Twilio Credentials**
   - Treat Auth Token like a password
   - Rotate periodically in Twilio Console
   - Never expose in client-side JavaScript

5. **Gmail App Passwords**
   - Use App Passwords, not account password
   - Revoke old passwords when no longer needed
   - One App Password per application

6. **Regular Updates**
   - Keep PHP updated for security patches
   - Update Python packages: `pip install --upgrade requests`
   - Monitor for Twilio API changes

7. **Database Access**
   - SQLite file should not be web-accessible
   - `.htaccess` rules prevent direct download
   - Regular backups recommended

---

## 💰 Pricing Information

### Twilio SMS Costs

| Region | Price per SMS | $15 Trial Gets You |
|--------|---------------|-------------------|
| Philippines | ~$0.0561 / msg | ~267 messages |
| Australia | ~$0.075 / msg | ~200 messages |
| United States | ~$0.0079 / msg | ~1,898 messages |
| United Kingdom | ~$0.055 / msg | ~272 messages |

Check current rates: [Twilio SMS Pricing](https://www.twilio.com/sms/pricing)

### Gmail

- **Free** - No limits on App Password usage
- Already included with your Google account

### Hosting

- **Development**: Free (MAMP/XAMPP on localhost)
- **Production**: 
  - Shared hosting: ~$5-10/month
  - VPS: ~$5-20/month
  - Raspberry Pi: One-time ~$35-75 (self-host)

---

## 🔄 Upgrading Twilio Trial

**Remove "Sent from your Twilio trial account" prefix:**

1. Go to [Twilio Console → Billing](https://console.twilio.com/billing)
2. Click **Upgrade Account**
3. Add payment method (credit card)
4. Account upgraded automatically
5. Prefix removed from all future SMS
6. You can keep balance at $0 - only pay for what you use

**Benefits of upgrading:**
- ✅ No "trial account" prefix in SMS
- ✅ Send to any number (no verification required)
- ✅ No geographic permission restrictions
- ✅ Higher sending limits
- ✅ Access to advanced features

---

## 🤝 Contributing

We welcome contributions! Here's how:

1. **Fork the repository**
   ```bash
   git clone https://github.com/ronspeedster/rpi_server_live_checker.git
   cd rpi_server_live_checker
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow existing code style
   - Test thoroughly
   - Update documentation if needed

3. **Submit a pull request**
   - Describe your changes
   - Link related issues
   - Ensure tests pass

**Areas for contribution:**
- 📱 Additional phone number formats (more countries)
- 🌐 Internalization (i18n)
- 📊 Advanced reporting/charts
- 🔔 Additional notification channels (Slack, Discord, etc.)
- 🎨 UI/UX improvements
- 📖 Documentation improvements

---

## 📝 License

This project is open source.  See LICENSE file for details.

---

## 💬 Support & Community

- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/ronspeedster/rpi_server_live_checker/issues)
- 💡 **Feature Requests**: [GitHub Discussions](https://github.com/ronspeedster/rpi_server_live_checker/discussions)
- 📖 **Documentation**: This README and inline code comments
- 📧 **Email Support**: Check repository for contact info

### Debugging Checklist

Before asking for help, please check:

1. ✅ Virtual environment activated
2. ✅ `requests` library installed
3. ✅ Configuration files created (`config.email.php`, `config.sms.php`)
4. ✅ Test pages working (`test_email.php`, `test_sms.php`)
5. ✅ Log file checked (`data/monitor.log`)
6. ✅ Browser console for JavaScript errors
7. ✅ PHP error logs

---

## 🎯 Quick Reference

### File Locations

- Database: `data/network_monitor.sqlite`
- Logs: `data/monitor.log`
- Email Config: `config.email.php`
- SMS Config: `config.sms.php`

### URLs

- Dashboard: `http://localhost/rpi_server_live_checker/`
- Email Test: `http://localhost/rpi_server_live_checker/test_email.php`
- SMS Test: `http://localhost/rpi_server_live_checker/test_sms.php`
- Monitor Control: `http://localhost/rpi_server_live_checker/monitor/control.php`

### Commands

```bash
# Activate virtual environment
.venv\Scripts\activate  # Windows
source .venv/bin/activate  # Linux/macOS

# Install dependencies
pip install requests

# Single ping check
python scripts/ping_devices.py

# Start continuous monitoring
python scripts/ping_devices.py --continuous --interval 60 --log

# View logs
tail -f data/monitor.log  # Linux/macOS
Get-Content data\monitor.log -Tail 20 -Wait  # Windows PowerShell
```

### Default Credentials

- Username: `admin`
- Password: `admin`
- ⚠️ **Change immediately after first login!**

---

## 🌟 Acknowledgments

- [SB Admin 2](https://startbootstrap.com/theme/sb-admin-2) - Dashboard theme
- [Twilio](https://www.twilio.com/) - SMS API
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Email inspiration
- All contributors and users!

---

**Built with ❤️ for keeping servers online** 🚀

**Happy Monitoring!** 📡✨