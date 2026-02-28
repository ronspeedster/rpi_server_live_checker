<?php
/**
 * Twilio SMS Test Page
 * Test your Twilio SMS configuration
 */

require_once __DIR__ . '/includes/SMSHelper.php';

// Handle test actions
$testPhone = $_GET['phone'] ?? '';
$testMessage = $_GET['message'] ?? 'This is a test message from RPi Server Live Checker.';
$sendTest = isset($_GET['send']);
$checkBalance = isset($_GET['balance']);

$balanceInfo = null;
$sendResult = null;

if ($checkBalance) {
    $balanceInfo = SMSHelper::checkBalance();
}

if ($sendTest && $testPhone) {
    $sendResult = SMSHelper::send($testPhone, $testMessage);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>SMS Test - Twilio</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f7fa;
            color: #333;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #f22f46;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        h1 .logo {
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin: 0 0 30px 0;
            font-size: 14px;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #f22f46;
            padding-bottom: 10px;
            margin: 30px 0 20px 0;
        }
        .info-box {
            background: #e8f4fd;
            border-left: 4px solid #0891b2;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .code {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            font-family: "Courier New", monospace;
            font-size: 13px;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #f22f46;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px 10px 0;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover { background: #d91f36; }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover { background: #4b5563; }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .form-group small {
            display: block;
            margin-top: 6px;
            color: #6b7280;
            font-size: 13px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #f22f46;
        }
        .balance-display {
            font-size: 42px;
            font-weight: bold;
            color: #22c55e;
            text-align: center;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            margin: 20px 0;
        }
        .balance-display .currency {
            font-size: 24px;
            color: #6b7280;
        }
        .balance-display .label {
            font-size: 14px;
            color: #6b7280;
            font-weight: normal;
            margin-top: 10px;
        }
        .config-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 10px;
            font-size: 14px;
        }
        .config-grid strong {
            color: #374151;
        }
        ul, ol {
            line-height: 1.8;
        }
        .step-number {
            display: inline-block;
            background: #f22f46;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 8px;
        }
        .footer-links {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <span class="logo">📱</span>
            <span>Twilio SMS Test</span>
        </h1>
        <p class="subtitle">Test your Twilio SMS configuration for RPi Server Live Checker</p>

        <?php if ($balanceInfo): ?>
            <?php if ($balanceInfo['success']): ?>
                <div class="success">
                    <strong>✅ Balance Retrieved Successfully</strong>
                </div>
                <div class="balance-display">
                    <div class="currency"><?php echo $balanceInfo['currency']; ?></div>
                    <div>$<?php echo number_format($balanceInfo['balance'], 2); ?></div>
                    <div class="label">Account Balance</div>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Failed to Retrieve Balance</strong><br>
                    <?php echo nl2br(htmlspecialchars($balanceInfo['message'])); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($sendResult): ?>
            <?php if ($sendResult['success']): ?>
                <div class="success">
                    <strong>✅ SMS Sent Successfully!</strong><br><br>
                    <div class="config-grid">
                        <strong>To:</strong> <span><?php echo htmlspecialchars($sendResult['data']['to']); ?></span>
                        <strong>From:</strong> <span><?php echo htmlspecialchars($sendResult['data']['from']); ?></span>
                        <strong>Status:</strong> <span><?php echo htmlspecialchars($sendResult['data']['status']); ?></span>
                        <strong>Message SID:</strong> <span><code><?php echo htmlspecialchars($sendResult['data']['sid']); ?></code></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Failed to Send SMS</strong><br><br>
                    <?php echo nl2br(htmlspecialchars($sendResult['message'])); ?>
                    <?php if (isset($sendResult['data']['error_code'])): ?>
                        <br><br>
                        <strong>Error Code:</strong> <?php echo htmlspecialchars($sendResult['data']['error_code']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="info-box">
            <strong>📋 Current Configuration</strong>
            <div class="config-grid" style="margin-top: 15px;">
                <strong>Account SID:</strong> <code><?php echo substr(TWILIO_ACCOUNT_SID, 0, 10); ?>...</code>
                <strong>Auth Token:</strong> <code><?php echo substr(TWILIO_AUTH_TOKEN, 0, 8); ?>...</code>
                <strong>From Number:</strong> <code><?php echo htmlspecialchars(TWILIO_FROM_NUMBER); ?></code>
            </div>
        </div>

        <h2>💰 Check Account Balance</h2>
        <p>Check your current Twilio account balance and available credits.</p>
        <a href="?balance=1" class="btn">Check Balance</a>

        <h2>📤 Send Test SMS</h2>
        
        <div class="error" style="border-left-color: #f59e0b; background: #fef3c7;">
            <strong>⚠️ IMPORTANT: Geographic Permissions Required!</strong><br><br>
            
            If you see error: <em>"Permission to send an SMS has not been enabled for the region indicated by the 'To' number"</em>
            
            <br><br><strong>Quick Fix (2 minutes):</strong>
            <ol style="margin: 10px 0 0 20px;">
                <li><strong>Go to:</strong> <a href="https://console.twilio.com/us1/develop/sms/settings/geo-permissions" target="_blank" style="color: #0891b2; font-weight: 600;">
                    Twilio Geographic Permissions →
                </a></li>
                <li><strong>Find "Philippines" and "Australia"</strong> in the country list</li>
                <li><strong>For each country, check all boxes:</strong> ✅ SMS, ✅ MMS, ✅ WhatsApp</li>
                <li><strong>Click "Save"</strong></li>
                <li><strong>Return here and try again!</strong></li>
            </ol>
        </div>
        
        <div class="warning">
            <strong>📝 Alternative: Verify Individual Numbers</strong><br>
            Instead of enabling geographic permissions, you can verify specific recipient numbers:
            <br><br>
            <a href="https://console.twilio.com/us1/develop/phone-numbers/manage/verified" target="_blank">Verify a phone number →</a>
        </div>

        <form method="GET">
            <div class="form-group">
                <label for="phone">📞 Recipient Phone Number</label>
                <input 
                    type="text" 
                    id="phone" 
                    name="phone" 
                    placeholder="+639171234567, 09171234567, +61412345678, or 0412345678" 
                    value="<?php echo htmlspecialchars($testPhone); ?>"
                    required
                >
                <small>Philippine: +639XXXXXXXXX or 09XXXXXXXXX | Australian: +61XXXXXXXXX or 04XXXXXXXX</small>
            </div>

            <div class="form-group">
                <label for="message">💬 Message</label>
                <textarea 
                    id="message" 
                    name="message" 
                    rows="3" 
                    placeholder="Enter your test message..."
                    required
                ><?php echo htmlspecialchars($testMessage); ?></textarea>
                <small>Standard SMS supports up to 160 characters per message</small>
            </div>

            <button type="submit" name="send" value="1" class="btn">
                📱 Send Test SMS
            </button>
        </form>

        <h2>🚀 Quick Setup Guide</h2>

        <h3><span class="step-number">1</span>Enable Geographic Permissions (Most Important!)</h3>
        <ol>
            <li>Go to <a href="https://console.twilio.com/us1/develop/sms/settings/geo-permissions" target="_blank"><strong>Geographic Permissions</strong></a></li>
            <li>Find <strong>"Philippines"</strong> and <strong>"Australia"</strong> in the list</li>
            <li>For each country, check the boxes: <strong>✅ SMS</strong>, <strong>✅ MMS</strong>, <strong>✅ WhatsApp</strong></li>
            <li>Click <strong>"Save"</strong></li>
            <li>Done! You can now send SMS to numbers in these countries</li>
        </ol>
        <div class="info-box">
            <strong>💡 Why is this needed?</strong> Twilio trial accounts have geographic restrictions by default. 
            Enabling specific countries allows you to send SMS to any number in those countries without verifying each one individually.
        </div>

        <h3><span class="step-number">2</span>Get Your Twilio Phone Number</h3>
        <ol>
            <li>Login to <a href="https://console.twilio.com/" target="_blank">Twilio Console</a></li>
            <li>Go to <strong>Phone Numbers</strong> → <strong>Manage</strong> → <strong>Active numbers</strong></li>
            <li>Copy your Twilio phone number (e.g., <code>+15551234567</code>)</li>
            <li>Update <code>TWILIO_FROM_NUMBER</code> in <code>config.sms.php</code></li>
        </ol>

        <h3><span class="step-number">3</span>Test the Integration</h3>
        <ol>
            <li>Click <strong>"Check Balance"</strong> above to verify API connection</li>
            <li>Enter any Philippine phone number (no verification needed after Step 1!)</li>
            <li>Click <strong>"Send Test SMS"</strong></li>
            <li>Check your phone for the message! 📱</li>
        </ol>

        <h2>💡 Important Notes</h2>

        <div class="info-box">
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Trial Credits:</strong> New accounts get $15 free credit</li>
                <li><strong>Philippines SMS Cost:</strong> ~$0.0561 USD per message</li>
                <li><strong>Phone Format:</strong> Use international format (+639XXXXXXXXX)</li>
                <li><strong>Trial Limitations:</strong> Can only send to verified numbers until you upgrade</li>
                <li><strong>Upgrade Account:</strong> Remove trial restrictions by adding payment method</li>
            </ul>
        </div>

        <h2>🔧 Troubleshooting</h2>

        <div class="warning">
            <strong>Common Issues & Solutions:</strong>
            <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                <li><strong>Error 21211 (Invalid To Number):</strong> Check phone number format, use +639XXXXXXXXX</li>
                <li><strong>Error 21212 (Invalid From Number):</strong> Update TWILIO_FROM_NUMBER in config.sms.php</li>
                <li><strong>Error 21606 (Not Verified):</strong> Verify the recipient's number in Twilio Console</li>
                <li><strong>Error 20003 (Auth Failed):</strong> Check Account SID and Auth Token in config.sms.php</li>
                <li><strong>No Balance:</strong> Add credits at <a href="https://console.twilio.com/billing" target="_blank">Twilio Billing</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
            <a href="https://console.twilio.com/" target="_blank" class="btn btn-secondary">Twilio Console →</a>
            <a href="https://www.twilio.com/docs/sms/quickstart/php" target="_blank" class="btn btn-secondary">API Docs →</a>
        </div>
    </div>
</body>
</html>
