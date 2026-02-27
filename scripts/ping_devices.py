#!/usr/bin/env python3
"""
Network Device Ping Monitor
Pings all active devices and logs results to the database.
Can run in single-shot mode or continuous monitoring mode.
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
from pathlib import Path
from datetime import datetime

# Get the database path
DB_PATH = Path(__file__).parent.parent / 'data' / 'network_monitor.sqlite'
LOG_FILE = Path(__file__).parent.parent / 'data' / 'monitor.log'
PID_FILE = Path(__file__).parent.parent / 'data' / 'monitor.pid'

# Global flag for graceful shutdown
running = True

def signal_handler(signum, frame):
    """Handle shutdown signals gracefully."""
    global running
    print(f"\n[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Received shutdown signal. Stopping...")
    running = False

def log_message(message, log_file=None):
    """Print message and optionally write to log file."""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    log_line = f"[{timestamp}] {message}"
    
    # Print to stdout with error handling for encoding issues
    try:
        print(log_line, flush=True)
    except UnicodeEncodeError:
        # Fallback for Windows console encoding issues
        print(log_line.encode('ascii', errors='replace').decode('ascii'), flush=True)
    
    if log_file:
        try:
            with open(log_file, 'a', encoding='utf-8') as f:
                f.write(log_line + '\n')
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
    ping_cmd = shutil.which('ping')
    if not ping_cmd:
        # Try common locations if not in PATH
        common_paths = ['/bin/ping', '/sbin/ping', '/usr/bin/ping', '/usr/sbin/ping']
        for path in common_paths:
            if Path(path).exists():
                ping_cmd = path
                break
    
    if not ping_cmd:
        return {
            'status': 'OFFLINE',
            'rtt_ms': None,
            'message': 'Ping command not found on system'
        }
    
    # Build ping command based on OS
    if system == 'windows':
        command = [ping_cmd, '-n', str(count), '-w', str(timeout * 1000), ip_address]
    else:  # Linux, macOS, Unix
        command = [ping_cmd, '-c', str(count), '-W', str(timeout), ip_address]
    
    try:
        # Execute ping command
        result = subprocess.run(
            command,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=timeout + 2
        )
        
        output = result.stdout + result.stderr
        
        # Check if ping was successful
        if result.returncode == 0:
            # Extract RTT (Round Trip Time) from output
            rtt_ms = extract_rtt(output, system)
            return {
                'status': 'ONLINE',
                'rtt_ms': rtt_ms,
                'message': f'Ping successful (RTT: {rtt_ms}ms)' if rtt_ms else 'Ping successful'
            }
        else:
            return {
                'status': 'OFFLINE',
                'rtt_ms': None,
                'message': 'Host unreachable or timeout'
            }
            
    except subprocess.TimeoutExpired:
        return {
            'status': 'OFFLINE',
            'rtt_ms': None,
            'message': 'Ping timeout'
        }
    except Exception as e:
        return {
            'status': 'OFFLINE',
            'rtt_ms': None,
            'message': f'Error: {str(e)}'
        }

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
        if system == 'windows':
            # Windows format: "Average = 10ms" or "Average = 10ms"
            match = re.search(r'Average\s*=\s*(\d+)ms', output, re.IGNORECASE)
            if match:
                return int(match.group(1))
            # Also try: "time=10ms"
            match = re.search(r'time[=<]\s*(\d+)ms', output, re.IGNORECASE)
            if match:
                return int(match.group(1))
        else:  # Linux, macOS, Unix
            # Format: "time=10.5 ms" or "min/avg/max = 10.5/20.3/30.1 ms"
            match = re.search(r'time=(\d+\.?\d*)\s*ms', output, re.IGNORECASE)
            if match:
                return int(float(match.group(1)))
            # Try avg from statistics
            match = re.search(r'min/avg/max.*?=\s*[\d.]+/([\d.]+)/', output, re.IGNORECASE)
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
        cursor.execute("""
            INSERT INTO ping_logs (device_id, status, rtt_ms, message)
            VALUES (?, ?, ?, ?)
        """, (device_id, status, rtt_ms, message))
        conn.commit()
    except sqlite3.Error as e:
        print(f"Error logging ping result for device {device_id}: {e}", file=sys.stderr)

def write_pid_file():
    """Write current process ID to file."""
    try:
        with open(PID_FILE, 'w') as f:
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
    cursor.execute("""
        SELECT id, name, ip_address
        FROM devices
        WHERE is_active = 1
        ORDER BY name
    """)
    
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
            
        device_id = device['id']
        name = device['name']
        ip_address = device['ip_address']
        
        log_message(f"  -> {name} ({ip_address})...", log_file)
        
        result = ping_device(ip_address)
        
        # Log to database
        log_ping_result(
            conn,
            device_id,
            result['status'],
            result['rtt_ms'],
            result['message']
        )
        
        # Log result
        status_icon = '[OK]' if result['status'] == 'ONLINE' else '[FAIL]'
        rtt_info = f" [{result['rtt_ms']}ms]" if result['rtt_ms'] else ""
        log_message(f"    {status_icon} {result['status']}{rtt_info} - {result['message']}", log_file)
    
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
                log_message(f"Waiting {interval} seconds until next cycle...\n", log_file)
                
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
        description='Network Device Ping Monitor',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  # Single ping check (default)
  python ping_devices.py
  
  # Continuous monitoring with 60 second interval
  python ping_devices.py --continuous --interval 60
  
  # Save output to log file
  python ping_devices.py --continuous --interval 30 --log
        """
    )
    
    parser.add_argument(
        '--continuous',
        action='store_true',
        help='Run in continuous monitoring mode'
    )
    
    parser.add_argument(
        '--interval',
        type=int,
        default=60,
        help='Interval between ping cycles in seconds (default: 60)'
    )
    
    parser.add_argument(
        '--log',
        action='store_true',
        help='Write output to log file'
    )
    
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

if __name__ == '__main__':
    main()
