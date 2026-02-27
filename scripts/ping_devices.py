#!/usr/bin/env python3
"""
Network Device Ping Monitor
Pings all active devices and logs results to the database.
Can run in single-shot mode or continuous monitoring mode.
Sends email/SMS alerts when devices are offline for 5+ minutes.
"""

import sqlite3
import subprocess
import platform
import re
import sys
import time
import argparse
import signal
import shutil
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from pathlib import Path
from datetime import datetime, timedelta

# Get the database path
DB_PATH = Path(__file__).parent.parent / "data" / "network_monitor.sqlite"
LOG_FILE = Path(__file__).parent.parent / "data" / "monitor.log"
PID_FILE = Path(__file__).parent.parent / "data" / "monitor.pid"

# Global flag for graceful shutdown
running = True


def signal_handler(signum, frame):
    """Handle shutdown signals gracefully."""
    global running
    print(
        f"\n[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Received shutdown signal. Stopping..."
    )
    running = False


def log_message(message, log_file=None):
    """Print message and optionally write to log file."""
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    log_line = f"[{timestamp}] {message}"

    # Print to stdout with error handling for encoding issues
    try:
        print(log_line, flush=True)
    except UnicodeEncodeError:
        # Fallback for Windows console encoding issues
        print(log_line.encode("ascii", errors="replace").decode("ascii"), flush=True)

    if log_file:
        try:
            with open(log_file, "a", encoding="utf-8") as f:
                f.write(log_line + "\n")
        except Exception as e:
            print(f"Error writing to log file: {e}", file=sys.stderr)


def get_db_connection():
    """Create and return a database connection."""
    try:
        conn = sqlite3.connect(str(DB_PATH))
        conn.row_factory = sqlite3.Row
        return conn
    except sqlite3.Error as e:
        print(f"Database connection error: {e}", file=sys.stderr)
        sys.exit(1)


def load_email_config():
    """Load email configuration from PHP config file."""
    config_file = Path(__file__).parent.parent / "config.email.php"
    config = {}

    try:
        with open(config_file, "r", encoding="utf-8") as f:
            content = f.read()

            # Parse PHP define() statements
            import re

            patterns = {
                "host": r"define\('SMTP_HOST',\s*'([^']+)'\)",
                "port": r"define\('SMTP_PORT',\s*(\d+)\)",
                "username": r"define\('SMTP_USERNAME',\s*'([^']+)'\)",
                "password": r"define\('SMTP_PASSWORD',\s*'([^']+)'\)",
                "from_email": r"define\('SMTP_FROM_EMAIL',\s*'([^']+)'\)",
                "from_name": r"define\('SMTP_FROM_NAME',\s*'([^']+)'\)",
            }

            for key, pattern in patterns.items():
                match = re.search(pattern, content)
                if match:
                    config[key] = match.group(1)

        return config
    except Exception as e:
        print(f"Warning: Could not load email config: {e}", file=sys.stderr)
        return None


def send_email_alert(to_email, device_name, device_ip, alert_type="offline"):
    """Send email alert for device status."""
    config = load_email_config()

    if not config:
        print(
            "Email config not available. Skipping email notification.", file=sys.stderr
        )
        return False

    try:
        # Create message
        msg = MIMEMultipart("alternative")
        msg["Subject"] = f"⚠️ Device Alert: {device_name} is {alert_type.upper()}"
        msg["From"] = (
            f"{config.get('from_name', 'Network Monitor')} <{config['from_email']}>"
        )
        msg["To"] = to_email

        # Create HTML body - using same template as EmailHelper.php
        color = "#dc3545"  # Red for offline
        icon = "⚠️"
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        
        html = f"""
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body {{ font-family: Arial, sans-serif; line-height: 1.6; color: #333; }}
                .container {{ max-width: 600px; margin: 0 auto; padding: 20px; }}
                .header {{ background: {color}; color: white; padding: 20px; border-radius: 5px 5px 0 0; }}
                .content {{ background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }}
                .footer {{ background: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 5px 5px; }}
                .detail {{ margin: 10px 0; }}
                .label {{ font-weight: bold; display: inline-block; width: 120px; }}
                .value {{ color: #495057; }}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0;'>{icon} Device Alert: {alert_type.upper()}</h2>
                </div>
                <div class='content'>
                    <h3>Device Status Change Detected</h3>
                    <div class='detail'>
                        <span class='label'>Device Name:</span>
                        <span class='value'>{device_name}</span>
                    </div>
                    <div class='detail'>
                        <span class='label'>IP Address:</span>
                        <span class='value'>{device_ip}</span>
                    </div>
                    <div class='detail'>
                        <span class='label'>Status:</span>
                        <span class='value' style='color: {color}; font-weight: bold;'>{alert_type.upper()}</span>
                    </div>
                    <div class='detail'>
                        <span class='label'>Time:</span>
                        <span class='value'>{timestamp}</span>
                    </div>
                </div>
                <div class='footer'>
                    This is an automated message from Network Monitor System.<br>
                    Please do not reply to this email.
                </div>
            </div>
        </body>
        </html>
        """

        # Create plain text version
        text = f"""
Network Device Alert

Device: {device_name}
IP Address: {device_ip}
Status: {alert_type.upper()}
Time: {timestamp}

This is an automated alert from your network monitoring system.
        """

        msg.attach(MIMEText(text, "plain"))
        msg.attach(MIMEText(html, "html"))

        # Send email
        with smtplib.SMTP(config["host"], int(config["port"])) as server:
            server.starttls()
            server.login(config["username"], config["password"])
            server.send_message(msg)

        return True

    except Exception as e:
        print(f"Error sending email: {e}", file=sys.stderr)
        return False


def check_and_alert_device(
    conn, device_id, device_name, device_ip, current_status, log_file=None
):
    """
    Check if device needs alerting based on offline duration.

    Logic:
    - If device is offline for 5+ minutes, create alert and send notification
    - If alert exists and 30+ minutes since last notification, send another
    - If device is back online, resolve the alert
    """
    cursor = conn.cursor()

    # Get device notification settings
    cursor.execute(
        """
        SELECT notify_email, notify_sms, notify_email_user_id, notify_sms_user_id
        FROM devices 
        WHERE id = ?
    """,
        (device_id,),
    )

    device_settings = cursor.fetchone()

    if not device_settings:
        return

    # Check if there's an active alert for this device
    cursor.execute(
        """
        SELECT id, first_detected_at, last_notified_at
        FROM alerts
        WHERE device_id = ? AND status = 'active'
    """,
        (device_id,),
    )

    active_alert = cursor.fetchone()

    if current_status == "OFFLINE":
        # Check ping history to see if device has been offline for 5+ minutes
        cursor.execute(
            """
            SELECT checked_at, status
            FROM ping_logs
            WHERE device_id = ?
            ORDER BY checked_at DESC
            LIMIT 10
        """,
            (device_id,),
        )

        recent_pings = cursor.fetchall()

        if not recent_pings:
            return

        # Check if all recent pings (within 5 minutes) are OFFLINE
        five_minutes_ago = datetime.now() - timedelta(minutes=5)
        offline_duration = None

        for ping in recent_pings:
            ping_time = datetime.strptime(ping["checked_at"], "%Y-%m-%d %H:%M:%S")
            if ping_time < five_minutes_ago:
                # Found a ping older than 5 minutes
                if ping["status"] == "OFFLINE":
                    offline_duration = datetime.now() - ping_time
                break

        # Device has been offline for 5+ minutes
        if offline_duration and offline_duration >= timedelta(minutes=5):
            if not active_alert:
                # Create new alert
                first_offline = recent_pings[-1]["checked_at"]
                cursor.execute(
                    """
                    INSERT INTO alerts (device_id, status, first_detected_at, last_notified_at)
                    VALUES (?, 'active', ?, ?)
                """,
                    (
                        device_id,
                        first_offline,
                        datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                    ),
                )

                conn.commit()

                log_message(
                    f"  ⚠️  ALERT CREATED: {device_name} offline for 5+ minutes",
                    log_file,
                )

                # Send notification
                send_notification(
                    cursor, device_id, device_name, device_ip, device_settings, log_file
                )

            else:
                # Check if 30 minutes passed since last notification
                last_notified = datetime.strptime(
                    active_alert["last_notified_at"], "%Y-%m-%d %H:%M:%S"
                )
                time_since_last_alert = datetime.now() - last_notified

                if time_since_last_alert >= timedelta(minutes=30):
                    # Send another notification
                    cursor.execute(
                        """
                        UPDATE alerts 
                        SET last_notified_at = ?
                        WHERE id = ?
                    """,
                        (
                            datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                            active_alert["id"],
                        ),
                    )

                    conn.commit()

                    log_message(
                        f"  ⚠️  RE-ALERT: {device_name} still offline after 30 minutes",
                        log_file,
                    )

                    # Send notification
                    send_notification(
                        cursor,
                        device_id,
                        device_name,
                        device_ip,
                        device_settings,
                        log_file,
                    )

    else:  # Device is ONLINE
        if active_alert:
            # Resolve the alert automatically
            cursor.execute(
                """
                UPDATE alerts 
                SET status = 'resolved', actioned_at = ?
                WHERE id = ?
            """,
                (datetime.now().strftime("%Y-%m-%d %H:%M:%S"), active_alert["id"]),
            )

            conn.commit()

            log_message(f"  ✓ ALERT RESOLVED: {device_name} is back online", log_file)


def send_notification(
    cursor, device_id, device_name, device_ip, device_settings, log_file=None
):
    """Send notification based on device settings."""

    # Send email notification if enabled
    if device_settings["notify_email"]:
        # Get recipient email(s)
        user_id = device_settings["notify_email_user_id"]

        if user_id == 0 or user_id is None:
            # Send to all users
            cursor.execute(
                "SELECT email FROM users WHERE email IS NOT NULL AND email != ''"
            )
            emails = [row["email"] for row in cursor.fetchall()]
        else:
            # Send to specific user
            cursor.execute("SELECT email FROM users WHERE id = ?", (user_id,))
            user = cursor.fetchone()
            emails = [user["email"]] if user and user["email"] else []

        # Send emails
        for email in emails:
            if send_email_alert(email, device_name, device_ip):
                log_message(f"    📧 Email sent to {email}", log_file)
            else:
                log_message(f"    ❌ Failed to send email to {email}", log_file)

    # SMS notification (placeholder for future implementation)
    if device_settings["notify_sms"]:
        log_message(
            f"    📱 SMS notification configured (not yet implemented)", log_file
        )


def ping_device(ip_address, count=1, timeout=2):
    """
    Ping a device and return the result.

    Args:
        ip_address: IP address to ping
        count: Number of ping packets to send
        timeout: Timeout in seconds

    Returns:
        dict: {'status': 'ONLINE'|'OFFLINE', 'rtt_ms': int|None, 'message': str}
    """
    system = platform.system().lower()

    # Find ping command (handles PATH issues when run from PHP)
    ping_cmd = shutil.which("ping")
    if not ping_cmd:
        # Try common locations if not in PATH
        common_paths = ["/bin/ping", "/sbin/ping", "/usr/bin/ping", "/usr/sbin/ping"]
        for path in common_paths:
            if Path(path).exists():
                ping_cmd = path
                break

    if not ping_cmd:
        return {
            "status": "OFFLINE",
            "rtt_ms": None,
            "message": "Ping command not found on system",
        }

    # Build ping command based on OS
    if system == "windows":
        command = [ping_cmd, "-n", str(count), "-w", str(timeout * 1000), ip_address]
    else:  # Linux, macOS, Unix
        command = [ping_cmd, "-c", str(count), "-W", str(timeout), ip_address]

    try:
        # Execute ping command
        result = subprocess.run(
            command,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=timeout + 2,
        )

        output = result.stdout + result.stderr

        # Check if ping was successful
        if result.returncode == 0:
            # Extract RTT (Round Trip Time) from output
            rtt_ms = extract_rtt(output, system)
            return {
                "status": "ONLINE",
                "rtt_ms": rtt_ms,
                "message": (
                    f"Ping successful (RTT: {rtt_ms}ms)"
                    if rtt_ms
                    else "Ping successful"
                ),
            }
        else:
            return {
                "status": "OFFLINE",
                "rtt_ms": None,
                "message": "Host unreachable or timeout",
            }

    except subprocess.TimeoutExpired:
        return {"status": "OFFLINE", "rtt_ms": None, "message": "Ping timeout"}
    except Exception as e:
        return {"status": "OFFLINE", "rtt_ms": None, "message": f"Error: {str(e)}"}


def extract_rtt(output, system):
    """
    Extract RTT (Round Trip Time) from ping output.

    Args:
        output: Ping command output
        system: Operating system name

    Returns:
        int: RTT in milliseconds or None
    """
    try:
        if system == "windows":
            # Windows format: "Average = 10ms" or "Average = 10ms"
            match = re.search(r"Average\s*=\s*(\d+)ms", output, re.IGNORECASE)
            if match:
                return int(match.group(1))
            # Also try: "time=10ms"
            match = re.search(r"time[=<]\s*(\d+)ms", output, re.IGNORECASE)
            if match:
                return int(match.group(1))
        else:  # Linux, macOS, Unix
            # Format: "time=10.5 ms" or "min/avg/max = 10.5/20.3/30.1 ms"
            match = re.search(r"time=(\d+\.?\d*)\s*ms", output, re.IGNORECASE)
            if match:
                return int(float(match.group(1)))
            # Try avg from statistics
            match = re.search(
                r"min/avg/max.*?=\s*[\d.]+/([\d.]+)/", output, re.IGNORECASE
            )
            if match:
                return int(float(match.group(1)))
    except (ValueError, AttributeError):
        pass

    return None


def log_ping_result(conn, device_id, status, rtt_ms, message):
    """
    Log a ping result to the database.

    Args:
        conn: Database connection
        device_id: Device ID
        status: 'ONLINE' or 'OFFLINE'
        rtt_ms: Round trip time in milliseconds (or None)
        message: Status message
    """
    try:
        cursor = conn.cursor()
        cursor.execute(
            """
            INSERT INTO ping_logs (device_id, status, rtt_ms, message)
            VALUES (?, ?, ?, ?)
        """,
            (device_id, status, rtt_ms, message),
        )
        conn.commit()
    except sqlite3.Error as e:
        print(f"Error logging ping result for device {device_id}: {e}", file=sys.stderr)


def write_pid_file():
    """Write current process ID to file."""
    try:
        with open(PID_FILE, "w") as f:
            f.write(str(sys.argv[0]))
        return True
    except Exception as e:
        print(f"Error writing PID file: {e}", file=sys.stderr)
        return False


def remove_pid_file():
    """Remove PID file."""
    try:
        if PID_FILE.exists():
            PID_FILE.unlink()
    except Exception as e:
        print(f"Error removing PID file: {e}", file=sys.stderr)


def ping_cycle(log_file=None):
    """Execute one complete ping cycle for all active devices."""
    global running

    if not running:
        return 0

    conn = get_db_connection()
    cursor = conn.cursor()

    # Fetch all active devices
    cursor.execute(
        """
        SELECT id, name, ip_address
        FROM devices
        WHERE is_active = 1
        ORDER BY name
    """
    )

    devices = cursor.fetchall()

    if not devices:
        log_message("No active devices found to ping.", log_file)
        conn.close()
        return 0

    log_message(f"Pinging {len(devices)} device(s)...", log_file)

    # Ping each device and log results
    for device in devices:
        if not running:
            break

        device_id = device["id"]
        name = device["name"]
        ip_address = device["ip_address"]

        log_message(f"  -> {name} ({ip_address})...", log_file)

        result = ping_device(ip_address)

        # Log to database
        log_ping_result(
            conn, device_id, result["status"], result["rtt_ms"], result["message"]
        )

        # Check and manage alerts for this device
        check_and_alert_device(
            conn, device_id, name, ip_address, result["status"], log_file
        )

        # Log result
        status_icon = "[OK]" if result["status"] == "ONLINE" else "[FAIL]"
        rtt_info = f" [{result['rtt_ms']}ms]" if result["rtt_ms"] else ""
        log_message(
            f"    {status_icon} {result['status']}{rtt_info} - {result['message']}",
            log_file,
        )

    conn.close()
    return len(devices)


def run_continuous(interval, log_file=None):
    """Run continuous monitoring with specified interval."""
    global running

    # Register signal handlers
    signal.signal(signal.SIGINT, signal_handler)
    signal.signal(signal.SIGTERM, signal_handler)

    # Write PID file
    write_pid_file()

    log_message("=" * 60, log_file)
    log_message("CONTINUOUS MONITORING MODE STARTED", log_file)
    log_message(f"Ping interval: {interval} seconds", log_file)
    log_message("Press Ctrl+C to stop", log_file)
    log_message("=" * 60, log_file)

    cycle_count = 0

    try:
        while running:
            cycle_count += 1
            log_message(f"\n--- Cycle #{cycle_count} ---", log_file)

            ping_cycle(log_file)

            if running:
                log_message(
                    f"Waiting {interval} seconds until next cycle...\n", log_file
                )

                # Sleep in small intervals to allow responsive shutdown
                for _ in range(interval):
                    if not running:
                        break
                    time.sleep(1)

    finally:
        log_message("=" * 60, log_file)
        log_message("CONTINUOUS MONITORING MODE STOPPED", log_file)
        log_message(f"Total cycles completed: {cycle_count}", log_file)
        log_message("=" * 60, log_file)
        remove_pid_file()


def run_single(log_file=None):
    """Run a single ping cycle."""
    log_message("Starting single ping check...", log_file)
    count = ping_cycle(log_file)
    log_message(f"Ping check completed. Checked {count} device(s).\n", log_file)


def main():
    """Main function with command-line argument support."""
    parser = argparse.ArgumentParser(
        description="Network Device Ping Monitor",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  # Single ping check (default)
  python ping_devices.py
  
  # Continuous monitoring with 60 second interval
  python ping_devices.py --continuous --interval 60
  
  # Save output to log file
  python ping_devices.py --continuous --interval 30 --log
        """,
    )

    parser.add_argument(
        "--continuous", action="store_true", help="Run in continuous monitoring mode"
    )

    parser.add_argument(
        "--interval",
        type=int,
        default=60,
        help="Interval between ping cycles in seconds (default: 60)",
    )

    parser.add_argument("--log", action="store_true", help="Write output to log file")

    args = parser.parse_args()

    # Validate interval
    if args.interval < 5:
        print("Error: Interval must be at least 5 seconds", file=sys.stderr)
        sys.exit(1)

    log_file = LOG_FILE if args.log else None

    # Clear old log file if in continuous mode
    if args.continuous and log_file and log_file.exists():
        try:
            log_file.unlink()
        except Exception:
            pass

    if args.continuous:
        run_continuous(args.interval, log_file)
    else:
        run_single(log_file)


if __name__ == "__main__":
    main()
