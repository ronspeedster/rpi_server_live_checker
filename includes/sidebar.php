<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_PATH; ?>dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-network-wired"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Network Monitor</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item<?php echo ($active_page === 'dashboard') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Monitoring
    </div>

    <!-- Nav Item - Monitor Control -->
    <li class="nav-item<?php echo ($active_page === 'monitor_control' || $active_page === 'monitor_logs') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>monitor/control.php">
            <i class="fas fa-fw fa-play-circle"></i>
            <span>Monitor Control</span></a>
    </li>

    <!-- Nav Item - Logs -->
    <li class="nav-item<?php echo ($active_page === 'logs') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>monitor/history.php">
            <i class="fas fa-fw fa-list"></i>
            <span>Ping Logs</span></a>
    </li>

    <!-- Nav Item - Alerts -->
    <li class="nav-item<?php echo ($active_page === 'alerts') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>monitor/alerts.php">
            <i class="fas fa-fw fa-exclamation-triangle"></i>
            <span>Alerts</span>
            <?php
            // Show active alert count badge
            try {
                $alertCount = db()->query("SELECT COUNT(*) FROM alerts WHERE status = 'active'")->fetchColumn();
                if ($alertCount > 0) {
                    echo '<span class="badge badge-danger badge-counter" style="margin-left: 5px;">' . $alertCount . '</span>';
                }
            } catch (PDOException $e) {
                // Silently fail if alerts table doesn't exist yet
            }
            ?>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    <!-- Nav Item - Devices -->
    <li class="nav-item<?php echo ($active_page === 'devices') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>devices/">
            <i class="fas fa-fw fa-server"></i>
            <span>Devices</span></a>
    </li>

    <!-- Nav Item - Users -->
    <li class="nav-item<?php echo ($active_page === 'users') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>users/">
            <i class="fas fa-fw fa-users"></i>
            <span>User Management</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Settings
    </div>

    <!-- Nav Item - Change Password -->
    <li class="nav-item<?php echo ($active_page === 'change_password') ? ' active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_PATH; ?>change_password.php">
            <i class="fas fa-fw fa-key"></i>
            <span>Change Password</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->
