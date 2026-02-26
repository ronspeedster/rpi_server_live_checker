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
    <link href="sb_admin_theme/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="sb_admin_theme/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Network Monitor</h1>
                                        <p class="mb-4">Welcome Back!</p>
                                    </div>

                                    <?php if ($error): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo htmlspecialchars($error); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($info): ?>
                                        <div class="alert alert-warning" role="alert">
                                            <?php echo htmlspecialchars($info); ?>
                                        </div>
                                    <?php endif; ?>

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
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="#" onclick="alert('Password reset feature coming soon!'); return false;">Forgot Password?</a>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <small class="text-muted">Default credentials: admin / admin</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="sb_admin_theme/vendor/jquery/jquery.min.js"></script>
    <script src="sb_admin_theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="sb_admin_theme/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="sb_admin_theme/js/sb-admin-2.min.js"></script>

</body>

</html>
