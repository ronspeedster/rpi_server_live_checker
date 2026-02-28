<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$info = '';

// Check for password change warning from session
if (isset($_SESSION['password_warning'])) {
    $info = $_SESSION['password_warning'];
    unset($_SESSION['password_warning']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id, username, password_hash, need_password_change, first_name, last_name FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['need_password_change'] = $user['need_password_change'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Network Monitor - Login</title>

    <!-- Custom fonts for this template-->
    <link href="<?php echo BASE_PATH; ?>sb_admin_theme/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?php echo BASE_PATH; ?>sb_admin_theme/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .login-header .icon-circle i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-header h1 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .login-form {
            padding: 2.5rem;
        }
        
        .form-control-user {
            font-size: 0.95rem;
            padding: 1rem 1.25rem;
        }
        
        .btn-login {
            padding: 0.9rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(102, 126, 234, 0.4);
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem;
            background-color: #f8f9fc;
            border-top: 1px solid #e3e6f0;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .default-creds {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 0.75rem;
            border-radius: 0.35rem;
            margin-top: 1rem;
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container login-container">
        <div class="login-card">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body p-0">
                    <div class="login-form">
                        <!-- Login Header -->
                        <div class="login-header">
                            <div class="icon-circle">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <h1 class="h3">Network Monitor</h1>
                            <p>Please sign in to continue</p>
                        </div>

                        <!-- Error/Info Messages -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($info): ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-info-circle mr-2"></i>
                                <?php echo htmlspecialchars($info); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form class="user" method="POST" action="login.php">
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user"
                                    id="username" name="username" 
                                    placeholder="Username" required autofocus>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-user"
                                    id="password" name="password" 
                                    placeholder="Password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-login btn-user btn-block">
                                Sign In
                            </button>
                        </form>

                        <!-- Default Credentials Info -->
                        <div class="default-creds">
                            <small>
                                <i class="fas fa-key mr-1"></i>
                                <strong>Default credentials:</strong> admin / admin
                            </small>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="login-footer">
                        <a href="#" onclick="alert('Password reset feature coming soon!'); return false;">
                            <i class="fas fa-question-circle mr-1"></i>Forgot Password?
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="text-center mt-4">
                <small class="text-white-50">&copy; 2026 Network Monitor. All rights reserved.</small>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?php echo BASE_PATH; ?>sb_admin_theme/vendor/jquery/jquery.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>sb_admin_theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?php echo BASE_PATH; ?>sb_admin_theme/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?php echo BASE_PATH; ?>sb_admin_theme/js/sb-admin-2.min.js"></script>

</body>

</html>
