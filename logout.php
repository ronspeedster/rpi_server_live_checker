<?php
require_once __DIR__ . '/config.php';

// Check if user still needs to change password before logging out
$show_warning = false;
if (isset($_SESSION['need_password_change']) && $_SESSION['need_password_change'] == 1) {
    $show_warning = true;
}

// Destroy session
session_destroy();

// Start a new session to display the warning message
session_start();

if ($show_warning) {
    $_SESSION['password_warning'] = 'You logged out without changing your default password. Please change it after logging in.';
}

header('Location: login.php');
exit;
