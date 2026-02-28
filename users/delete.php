<?php
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    $_SESSION['user_error'] = 'Invalid user ID';
    header('Location: index.php');
    exit;
}

// Prevent users from deleting themselves
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['user_error'] = 'You cannot delete your own account';
    header('Location: index.php');
    exit;
}

try {
    $pdo = db();
    
    // Fetch user info before deleting
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['user_error'] = 'User not found';
        header('Location: index.php');
        exit;
    }
    
    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    
    $_SESSION['user_deleted'] = 'User "' . $user['username'] . '" has been deleted successfully';
} catch (Exception $e) {
    $_SESSION['user_error'] = 'Failed to delete user: ' . $e->getMessage();
}

header('Location: index.php');
exit;
