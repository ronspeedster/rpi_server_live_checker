# Ping Monitor Script

## ⚠️ Prerequisites - Virtual Environment Setup

**IMPORTANT**: This script requires Python packages that are NOT installed by default. You must set up a virtual environment first.

### First-Time Setup

```bash
# 1. Navigate to the PROJECT ROOT (not this scripts folder!)
cd ..  # Go to rpi_server_live_checker/

# 2. Create virtual environment
python -m venv .venv

# 3. Activate virtual environment
.venv\Scripts\activate  # Windows
# source .venv/bin/activate  # Linux/macOS

# 4. Install required packages
pip install requests

# 5. Verify installation
python -c "import requests; print('✅ Requests library installed!')"
```

### Before Running the Script

**Always activate the virtual environment first:**

```bash
# Windows
C:\MAMP\htdocs\rpi_server_live_checker\.venv\Scripts\activate

# Linux/macOS
source /path/to/rpi_server_live_checker/.venv/bin/activate

# You should see (.venv) in your terminal prompt
```

### Why Virtual Environment?

The monitoring script needs:
- `requests` library for Twilio SMS integration
- `smtplib` for Email alerts (built-in)
- Other dependencies isolated from system Python

Without the virtual environment, you'll see:
```
ModuleNotFoundError: No module named 'requests'
```

---

## Usage

### Single Ping Check
Run once and exit:
```bash
# Make sure virtual environment is activated!
python ping_devices.py
```

### Continuous Monitoring
Run continuously with auto ping at intervals:
```bash
# Make sure virtual environment is activated!

# 60 second interval with log file
python ping_devices.py --continuous --interval 60 --log

# 30 second interval without log file
python ping_devices.py --continuous --interval 30
```

### Command Line Arguments

- `--continuous`: Enable continuous monitoring mode (runs until stopped)
- `--interval SECONDS`: Set ping interval in seconds (default: 60, minimum: 5)
- `--log`: Write output to `../data/monitor.log` file

### Examples

```bash
# Quick single check
python ping_devices.py

# Start 24/7 monitoring with 2 minute intervals
python ping_devices.py --continuous --interval 120 --log

# Fast monitoring every 10 seconds
python ping_devices.py --continuous --interval 10 --log
```

---

## Web Interface (Recommended)

The easiest way to control monitoring is through the web interface - it automatically uses the virtual environment:

1. Go to **Monitor Control** page in the dashboard
2. Click **Start Monitoring**
3. Set your desired interval
4. Click **View Live Logs** to watch real-time output

**The web interface automatically handles:**
- Virtual environment activation
- Background process management
- Log file rotation
- Clean shutdown

## Stopping the Service

### From Web Interface
Go to Monitor Control page and click "Stop Monitoring"

### From Command Line
Press `Ctrl+C` if running in foreground, or:
```bash
# macOS/Linux
pkill -f ping_devices.py

# Windows
taskkill /F /IM python.exe
```

## Files Created

- `monitor.log` - Live log output (in data/ directory)
- `monitor.pid` - Process ID file (in data/ directory)

## Requirements

- Python 3.6+
- SQLite database with devices table
- Network access to ping devices
