<?php
/**
 * SMS Helper Class - Twilio Integration
 * Simple and clean SMS sending via Twilio API
 */

require_once __DIR__ . '/../config.sms.php';

class SMSHelper
{
    /**
     * Send an SMS using Twilio API
     * 
     * @param string $to Recipient phone number (format: +639171234567)
     * @param string $message SMS message content
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public static function send($to, $message)
    {
        try {
            // Normalize phone number to international format (+639XX)
            $to = self::normalizePhoneNumber($to);
            
            if (!$to) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format. Use 09XXXXXXXXX or +639XXXXXXXXX',
                    'data' => null
                ];
            }

            // Twilio API endpoint
            $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';

            // Prepare POST data
            $data = [
                'From' => TWILIO_FROM_NUMBER,
                'To' => $to,
                'Body' => $message
            ];

            // Send request with HTTP Basic Auth
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            // For local development - disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Connection error: ' . $curlError,
                    'data' => null
                ];
            }

            $result = json_decode($response, true);

            // Success: HTTP 200 or 201
            if ($httpCode === 200 || $httpCode === 201) {
                return [
                    'success' => true,
                    'message' => 'SMS sent successfully!',
                    'data' => [
                        'sid' => $result['sid'] ?? null,
                        'status' => $result['status'] ?? 'unknown',
                        'to' => $result['to'] ?? $to,
                        'from' => $result['from'] ?? TWILIO_FROM_NUMBER
                    ]
                ];
            }

            // Error response
            $errorMsg = 'Unknown error';
            if (isset($result['message'])) {
                $errorMsg = $result['message'];
            } elseif (isset($result['error_message'])) {
                $errorMsg = $result['error_message'];
            }

            // Common error codes
            $errorCode = $result['code'] ?? null;
            $errorDetails = self::getTwilioErrorDetails($errorCode);

            return [
                'success' => false,
                'message' => $errorMsg . ($errorDetails ? "\n\n" . $errorDetails : ''),
                'data' => [
                    'error_code' => $errorCode,
                    'raw_response' => $result
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Normalize phone number to international format
     * Supports Philippine and Australian numbers
     * 
     * @param string $phone Phone number
     * @return string|false Normalized number (e.g., +639XXXXXXXXX or +61XXXXXXXXX) or false if invalid
     */
    private static function normalizePhoneNumber($phone)
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // ===== PHILIPPINE NUMBERS =====
        // 09XXXXXXXXX -> +639XXXXXXXXX
        if (preg_match('/^0(9\d{9})$/', $phone, $matches)) {
            return '+63' . $matches[1];
        }

        // +639XXXXXXXXX (already correct)
        if (preg_match('/^\+639\d{9}$/', $phone)) {
            return $phone;
        }

        // 639XXXXXXXXX -> +639XXXXXXXXX
        if (preg_match('/^(639\d{9})$/', $phone)) {
            return '+' . $phone;
        }

        // 9XXXXXXXXX -> +639XXXXXXXXX
        if (preg_match('/^(9\d{9})$/', $phone)) {
            return '+63' . $phone;
        }

        // ===== AUSTRALIAN NUMBERS =====
        // 04XXXXXXXX -> +614XXXXXXXX (10 digits starting with 04)
        if (preg_match('/^0(4\d{8})$/', $phone, $matches)) {
            return '+61' . $matches[1];
        }

        // +614XXXXXXXX (already correct)
        if (preg_match('/^\+614\d{8}$/', $phone)) {
            return $phone;
        }

        // 614XXXXXXXX -> +614XXXXXXXX
        if (preg_match('/^(614\d{8})$/', $phone)) {
            return '+' . $phone;
        }

        // 4XXXXXXXX -> +614XXXXXXXX (9 digits starting with 4)
        if (preg_match('/^(4\d{8})$/', $phone)) {
            return '+61' . $phone;
        }

        return false; // Invalid format
    }

    /**
     * Get detailed error information for common Twilio error codes
     * 
     * @param int|null $errorCode Twilio error code
     * @return string|null Error details
     */
    private static function getTwilioErrorDetails($errorCode)
    {
        $errors = [
            21211 => 'Invalid "To" phone number. Check the recipient phone number format.',
            21212 => 'Invalid "From" phone number. Update TWILIO_FROM_NUMBER in config.sms.php with your Twilio number.',
            21606 => 'Phone number not verified (Trial Account). Go to Twilio Console > Phone Numbers > Verified Caller IDs to verify this number.',
            21608 => 'Permission denied. The number may be on a blocklist or your account lacks permissions.',
            21610 => 'Message cannot be sent to landline or unreachable number.',
            20003 => 'Authentication failed. Check your Account SID and Auth Token in config.sms.php.',
        ];

        return $errors[$errorCode] ?? null;
    }

    /**
     * Check Twilio account balance
     * 
     * @return array ['success' => bool, 'balance' => float|null, 'message' => string]
     */
    public static function checkBalance()
    {
        try {
            $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Balance.json';

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return [
                    'success' => false,
                    'balance' => null,
                    'message' => 'Connection error: ' . $curlError,
                    'data' => null
                ];
            }

            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result['balance'])) {
                return [
                    'success' => true,
                    'balance' => floatval($result['balance']),
                    'currency' => $result['currency'] ?? 'USD',
                    'message' => 'Balance retrieved successfully',
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'balance' => null,
                'message' => 'Failed to retrieve balance',
                'data' => $result
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'balance' => null,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create alert message for device monitoring
     * 
     * @param string $deviceName Device name
     * @param string $ipAddress Device IP address
     * @param string $status Status (ONLINE/OFFLINE)
     * @return string Alert message
     */
    public static function createAlertSMS($deviceName, $ipAddress, $status)
    {
        $timestamp = date('M d, h:i A');
        return "ALERT: $deviceName ($ipAddress) is $status at $timestamp - RPi Server Live Checker";
    }
}
?>
