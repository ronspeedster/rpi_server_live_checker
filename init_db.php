<?php
// init_db.php

function table_exists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:t");
    $stmt->execute([':t' => $table]);
    return (bool)$stmt->fetchColumn();
}

function init_db(PDO $pdo): void {
    // If users table exists, assume schema already initialized
    if (table_exists($pdo, 'users')) return;

    // Users
    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            first_name TEXT NOT NULL DEFAULT 'Default First Name',
            last_name TEXT NOT NULL DEFAULT 'Default Last Name',
            email TEXT,
            phone TEXT,
            role TEXT NOT NULL DEFAULT 'admin',
            need_password_change INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");

    // Devices
    $pdo->exec("
        CREATE TABLE devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            ip_address TEXT NOT NULL UNIQUE,
            notes TEXT,
            is_active INTEGER NOT NULL DEFAULT 1,
            notify_email INTEGER NOT NULL DEFAULT 1,
            notify_sms INTEGER NOT NULL DEFAULT 1,
            notify_email_user_id INTEGER,
            notify_sms_user_id INTEGER,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (notify_email_user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (notify_sms_user_id) REFERENCES users(id) ON DELETE SET NULL
        );
    ");

    // Ping logs
    $pdo->exec("
        CREATE TABLE ping_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            device_id INTEGER NOT NULL,
            status TEXT NOT NULL CHECK (status IN ('ONLINE','OFFLINE')),
            rtt_ms INTEGER,
            message TEXT,
            checked_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
        );
    ");

    // Alerts table for offline device notifications
    $pdo->exec("
        CREATE TABLE alerts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            device_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT 'active',
            first_detected_at TEXT NOT NULL,
            last_notified_at TEXT,
            actioned_at TEXT,
            actioned_by INTEGER,
            notes TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
            FOREIGN KEY (actioned_by) REFERENCES users(id) ON DELETE SET NULL
        );
    ");

    // Indexes
    $pdo->exec("CREATE INDEX idx_ping_logs_device_time ON ping_logs(device_id, checked_at);");
    $pdo->exec("CREATE INDEX idx_ping_logs_time ON ping_logs(checked_at);");

    // Seed default admin user (change password immediately)
    $defaultUser = 'admin';
    $defaultPass = 'admin'; // <-- MUST change after first login
    $hash = password_hash($defaultPass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users(username, password_hash, first_name, last_name, role, need_password_change) VALUES (:u, :p, 'Default First Name', 'Default Last Name', 'admin', 1)");
    $stmt->execute([':u' => $defaultUser, ':p' => $hash]);
}