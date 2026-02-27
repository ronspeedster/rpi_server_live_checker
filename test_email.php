<?php
/**
 * Email Test Script
 * Test your email configuration before using it in production
 * 
 * Access this file via: http://localhost/rpi_server_live_checker/test_email.php
 */

require_once __DIR__ . '/includes/EmailHelper.php';

// Get test email from query parameter or use default
$testEmail = $_GET['email'] ?? SMTP_FROM_EMAIL;
$sendTest = isset($_GET['send']);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px 10px 0;
        }
        .btn:hover { background: #0056b3; }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        ol { line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Test</h1>
        
        <?php if ($sendTest): ?>
            <?php
            // Send test email
            $subject = "Test Email from RPi Server Live Checker";
            $body = EmailHelper::createAlertEmailBody(
                "Test Device",
                "192.168.1.100",
                "OFFLINE",
                "This is a test email to verify your email configuration is working correctly."
            );
            
            $result = EmailHelper::send($testEmail, $subject, $body);
            
            if ($result['success']): ?>
                <div class="success">
                    <strong>✅ Success!</strong><br>
                    Test email sent to: <strong><?php echo htmlspecialchars($testEmail); ?></strong><br>
                    Message: <?php echo htmlspecialchars($result['message']); ?>
                    <br><br>
                    Check your inbox (and spam folder) for the test email.
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Failed!</strong><br>
                    <?php echo htmlspecialchars($result['message']); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>Current Configuration:</strong><br>
            SMTP Host: <code><?php echo htmlspecialchars(SMTP_HOST); ?></code><br>
            SMTP Port: <code><?php echo htmlspecialchars(SMTP_PORT); ?></code><br>
            SMTP User: <code><?php echo htmlspecialchars(SMTP_USERNAME); ?></code><br>
            From Email: <code><?php echo htmlspecialchars(SMTP_FROM_EMAIL); ?></code><br>
            From Name: <code><?php echo htmlspecialchars(SMTP_FROM_NAME); ?></code><br>
        </div>
        
        <h2>Send Test Email</h2>
        <form method="GET">
            <div class="form-group">
                <label for="email">Test Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($testEmail); ?>" required>
            </div>
            <button type="submit" name="send" value="1" class="btn">Send Test Email</button>
        </form>
        
        <hr style="margin: 30px 0;">
        
        <h2>📝 Setup Instructions</h2>
        
        <h3>Step 1: Install PHPMailer (Recommended)</h3>
        <p>PHPMailer provides reliable SMTP email sending. Install it using Composer:</p>
        <div class="code">composer require phpmailer/phpmailer</div>
        
        <p><strong>Or download manually:</strong></p>
        <ol>
            <li>Download from: <a href="https://github.com/PHPMailer/PHPMailer/releases" target="_blank">https://github.com/PHPMailer/PHPMailer/releases</a></li>
            <li>Extract to <code>rpi_server_live_checker/vendor/</code></li>
        </ol>
        
        <h3>Step 2: Get Gmail App Password</h3>
        <ol>
            <li>Go to <a href="https://myaccount.google.com/" target="_blank">Google Account Settings</a></li>
            <li>Click "Security" in the left menu</li>
            <li>Under "How you sign in to Google", enable "2-Step Verification" (if not already enabled)</li>
            <li>After enabling 2-Step Verification, go back to Security</li>
            <li>Click "App passwords" (appears only after 2-Step is enabled)</li>
            <li>Select "Mail" as the app and "Windows Computer" (or Other) as the device</li>
            <li>Click "Generate"</li>
            <li>Copy the 16-character password (e.g., <code>abcd efgh ijkl mnop</code>)</li>
        </ol>
        
        <h3>Step 3: Configure Email Settings</h3>
        <p>Edit the file: <code>config.email.php</code></p>
        <div class="code">// Replace these values with your Gmail credentials
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password-here'); // 16-char App Password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'RPi Server Live Checker');</div>
        
        <h3>Step 4: Test Your Configuration</h3>
        <p>Use the form above to send a test email and verify everything is working.</p>
        
        <div class="info-box">
            <strong>⚠️ Security Tips:</strong><br>
            • Never use your regular Gmail password - always use App Password<br>
            • Never commit <code>config.email.php</code> with real credentials to Git<br>
            • Add it to <code>.gitignore</code><br>
            • Keep file permissions restricted to web server only
        </div>
        
        <h3>Troubleshooting</h3>
        <ul>
            <li><strong>Authentication failed:</strong> Make sure you're using an App Password, not your regular password</li>
            <li><strong>Connection timeout:</strong> Check your firewall allows outbound SMTP (port 587)</li>
            <li><strong>Could not authenticate:</strong> Verify 2-Step Verification is enabled on your Google account</li>
            <li><strong>PHPMailer not found:</strong> Install PHPMailer using the instructions above</li>
        </ul>
    </div>
</body>
</html>
