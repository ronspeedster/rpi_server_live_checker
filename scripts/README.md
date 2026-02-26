# Ping Monitor Script

## Usage

### Single Ping Check
Run once and exit:
```bash
python3 ping_devices.py
```

### Continuous Monitoring
Run continuously with auto ping at intervals:
```bash
# 60 second interval with log file
python3 ping_devices.py --continuous --interval 60 --log

# 30 second interval without log file
python3 ping_devices.py --continuous --interval 30
```

### Command Line Arguments

- `--continuous`: Enable continuous monitoring mode (runs until stopped)
- `--interval SECONDS`: Set ping interval in seconds (default: 60, minimum: 5)
- `--log`: Write output to `../data/monitor.log` file

### Examples

```bash
# Quick single check
python3 ping_devices.py

# Start 24/7 monitoring with 2 minute intervals
python3 ping_devices.py --continuous --interval 120 --log

# Fast monitoring every 10 seconds
python3 ping_devices.py --continuous --interval 10 --log
```

## Web Interface

The easiest way to control monitoring is through the web interface:

1. Go to **Monitor Control** page
2. Click **Start Monitoring**
3. Set your desired interval
4. Click **View Live Logs** to watch real-time output

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
