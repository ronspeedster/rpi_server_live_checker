<?php
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$username = $_SESSION['username'];
$first_name = $_SESSION['first_name'] ?? 'User';
$last_name = $_SESSION['last_name'] ?? '';
$need_password_change = $_SESSION['need_password_change'] ?? 0;

// Page settings
$page_title = 'Network Monitor - Live Logs';
$active_page = 'monitor_logs';
$page_styles = '
<style>
#log-container {
    background-color: #1a1a1a;
    color: #00ff00;
    font-family: "Courier New", Courier, monospace;
    font-size: 13px;
    padding: 15px;
    height: 600px;
    overflow-y: auto;
    border-radius: 4px;
    white-space: pre-wrap;
    word-wrap: break-word;
}

#log-container .log-line {
    margin-bottom: 2px;
    line-height: 1.4;
}

#log-container .log-online {
    color: #00ff00;
}

#log-container .log-offline {
    color: #ff4444;
}

#log-container .log-header {
    color: #ffaa00;
    font-weight: bold;
}

#log-container .log-timestamp {
    color: #888;
}

.status-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}

.status-connected {
    background-color: #28a745;
    box-shadow: 0 0 5px #28a745;
}

.status-disconnected {
    background-color: #dc3545;
}

#auto-scroll-toggle {
    cursor: pointer;
}
</style>
';

require_once __DIR__ . '/../includes/header.php';
?>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <span class="status-indicator" id="status-indicator"></span>
                            Live Monitor Logs
                        </h1>
                        <div>
                            <button class="btn btn-secondary" id="clear-logs">
                                <i class="fas fa-eraser"></i> Clear
                            </button>
                            <a href="control.php" class="btn btn-primary">
                                <i class="fas fa-cog"></i> Control Panel
                            </a>
                        </div>
                    </div>

                    <!-- Log Viewer -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Log Output</h6>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="auto-scroll-toggle" checked>
                                <label class="custom-control-label" for="auto-scroll-toggle">Auto-scroll</label>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="log-container">
                                <div class="log-line log-header">Connecting to log stream...</div>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>
                                <span id="connection-status">Connecting...</span> | 
                                <span id="log-count">0</span> lines | 
                                Last update: <span id="last-update">Never</span>
                            </small>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        This page shows real-time output from the monitoring service. 
                        If no logs appear, make sure the monitoring service is running from the 
                        <a href="control.php">Control Panel</a>.
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

<?php
$page_scripts = '
<script>
let logContainer = document.getElementById("log-container");
let statusIndicator = document.getElementById("status-indicator");
let connectionStatus = document.getElementById("connection-status");
let logCount = 0;
let autoScroll = true;
let lastPosition = 0;

// Toggle auto-scroll
document.getElementById("auto-scroll-toggle").addEventListener("change", function() {
    autoScroll = this.checked;
});

// Clear logs
document.getElementById("clear-logs").addEventListener("click", function() {
    logContainer.innerHTML = "";
    logCount = 0;
    document.getElementById("log-count").textContent = "0";
});

// Scroll to bottom
function scrollToBottom() {
    if (autoScroll) {
        logContainer.scrollTop = logContainer.scrollHeight;
    }
}

// Format log line with colors
function formatLogLine(line) {
    let className = "log-line";
    
    if (line.includes("✓") || line.includes("ONLINE")) {
        className += " log-online";
    } else if (line.includes("✗") || line.includes("OFFLINE")) {
        className += " log-offline";
    } else if (line.includes("===") || line.includes("---") || line.includes("Cycle")) {
        className += " log-header";
    }
    
    return `<div class="${className}">${escapeHtml(line)}</div>`;
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// Update status
function updateStatus(connected) {
    if (connected) {
        statusIndicator.className = "status-indicator status-connected";
        connectionStatus.textContent = "Connected";
        connectionStatus.className = "text-success";
    } else {
        statusIndicator.className = "status-indicator status-disconnected";
        connectionStatus.textContent = "Disconnected";
        connectionStatus.className = "text-danger";
    }
}

// Poll for new logs
function pollLogs() {
    fetch("stream_logs.php?position=" + lastPosition)
        .then(response => {
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.content) {
                const lines = data.content.split(/\r?\n/);
                lines.forEach(line => {
                    if (line.trim()) {
                        logContainer.innerHTML += formatLogLine(line);
                        logCount++;
                    }
                });
                
                lastPosition = data.position;
                document.getElementById("log-count").textContent = logCount;
                document.getElementById("last-update").textContent = new Date().toLocaleTimeString();
                
                scrollToBottom();
            }
            
            updateStatus(true);
        })
        .catch(error => {
            console.error("Error fetching logs:", error);
            updateStatus(false);
        });
}

// Start polling
updateStatus(false);
pollLogs(); // Initial load
setInterval(pollLogs, 1000); // Poll every second
</script>
';

require_once __DIR__ . '/../includes/footer.php';
?>
