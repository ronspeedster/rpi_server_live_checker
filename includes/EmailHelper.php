<?php
/**
 * Email Helper Class
 * Handles sending emails via SMTP (Gmail)
 * 
 * Uses PHPMailer library for robust email sending
 * 
 * Installation Option 1 (Composer):
 *   composer require phpmailer/phpmailer
 * 
 * Installation Option 2 (Manual - for MAMP users):
 *   1. Download from: https://github.com/PHPMailer/PHPMailer/releases
 *   2. Extract and place in: c:\MAMP\htdocs\rpi_server_live_checker\PHPMailer
 */

require_once __DIR__ . '/../config.email.php';

class EmailHelper {
    
    /**
     * Send an email using SMTP
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body (HTML supported)
     * @param bool $isHtml Whether body is HTML (default: true)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function send($to, $subject, $body, $isHtml = true) {
        // Check if PHPMailer files exist (before trying to load the class)
        $rootDir = dirname(__DIR__);
        $hasComposer = file_exists($rootDir . '/vendor/autoload.php');
        $hasManual = file_exists($rootDir . '/PHPMailer/src/PHPMailer.php');
        
        if ($hasComposer || $hasManual) {
            return self::sendWithPHPMailer($to, $subject, $body, $isHtml);
        } else {
            return self::sendWithMail($to, $subject, $body, $isHtml);
        }
    }
    
    /**
     * Send email using PHPMailer (recommended)
     */
    private static function sendWithPHPMailer($to, $subject, $body, $isHtml) {
        try {
            // Check if PHPMailer exists - try Composer first, then manual installation
            $rootDir = dirname(__DIR__);
            
            if (file_exists($rootDir . '/vendor/autoload.php')) {
                // Composer installation
                require $rootDir . '/vendor/autoload.php';
            } elseif (file_exists($rootDir . '/PHPMailer/src/PHPMailer.php')) {
                // Manual installation
                require $rootDir . '/PHPMailer/src/PHPMailer.php';
                require $rootDir . '/PHPMailer/src/SMTP.php';
                require $rootDir . '/PHPMailer/src/Exception.php';
            } else {
                return [
                    'success' => false,
                    'message' => 'PHPMailer not installed. Download from: https://github.com/PHPMailer/PHPMailer/releases'
                ];
            }
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            if (is_array($to)) {
                foreach ($to as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($to);
            }
            
            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($isHtml) {
                // Create plain text version
                $mail->AltBody = strip_tags($body);
            }
            
            $mail->send();
            
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send email using native PHP mail() with custom headers
     * Note: Requires server to have mail() configured with SMTP
     */
    private static function sendWithMail($to, $subject, $body, $isHtml) {
        $headers = [];
        $headers[] = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>';
        $headers[] = 'Reply-To: ' . SMTP_FROM_EMAIL;
        
        if ($isHtml) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }
        
        $toAddress = is_array($to) ? implode(', ', $to) : $to;
        
        $success = mail($toAddress, $subject, $body, implode("\r\n", $headers));
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Email failed: Unable to send via mail()'
            ];
        }
    }
    
    /**
     * Create a nicely formatted alert email body
     * 
     * @param string $deviceName Device name
     * @param string $ipAddress Device IP
     * @param string $status ONLINE or OFFLINE
     * @param string $message Additional message
     * @return string HTML email body
     */
    public static function createAlertEmailBody($deviceName, $ipAddress, $status, $message = '') {
        $color = $status === 'OFFLINE' ? '#dc3545' : '#28a745';
        $icon = $status === 'OFFLINE' ? '⚠️' : '✅';
        $timestamp = date('Y-m-d H:i:s');
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: $color; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
                .footer { background: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 5px 5px; }
                .detail { margin: 10px 0; }
                .label { font-weight: bold; display: inline-block; width: 120px; }
                .value { color: #495057; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0;'>$icon Device Alert: $status</h2>
                </div>
                <div class='content'>
                    <h3>Device Status Change Detected</h3>
                    <div class='detail'>
                        <span class='label'>Device Name:</span>
                        <span class='value'>$deviceName</span>
                    </div>
                    <div class='detail'>
                        <span class='label'>IP Address:</span>
                        <span class='value'>$ipAddress</span>
                    </div>
                    <div class='detail'>
                        <span class='label'>Status:</span>
                        <span class='value' style='color: $color; font-weight: bold;'>$status</span>
                    </div>
                    <div class='detail'>
                        <span class='label'>Time:</span>
                        <span class='value'>$timestamp</span>
                    </div>
                    " . ($message ? "<div class='detail'>
                        <span class='label'>Details:</span>
                        <span class='value'>$message</span>
                    </div>" : "") . "
                </div>
                <div class='footer'>
                    This is an automated message from Network Monitor System.<br>
                    Please do not reply to this email.
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $html;
    }
}
