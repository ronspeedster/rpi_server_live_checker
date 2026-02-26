<?php
require_once __DIR__ . '/config.php';

// Initialize database (creates tables if they don't exist)
db();

// If database is initialized, redirect to login
if (file_exists(DB_PATH)) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Network Monitor - Setup</title></head>
<body>
  <h1>Network Monitor - Setup</h1>
  <p>Setting up database...</p>
</body>
</html>