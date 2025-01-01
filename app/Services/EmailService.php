<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    private $logger;
    private $timezone;

    public function __construct() {
        // Set timezone from global function
        $this->timezone = get_current_timezone();
        
        // Configure PHPMailer with localized settings
        $this->mail = new PHPMailer(true);
        $this->logger = new \App\Services\LoggerService('email');
        
        // Configure SMTP with country-specific settings
        $this->configureSMTP();
    }

    private function configureSMTP() {
        try {
            // Reset configuration
            $this->mail->clearAllRecipients();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();

            // SMTP Configuration for Hostinger
            $this->mail->isSMTP();
            
            // Detailed debugging
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;  
            $this->mail->Debugoutput = function($str, $level) {
                $this->logger->debug('SMTP Debug', [
                    'level' => $level,
                    'message' => $str
                ]);
            };

            // Hostinger SMTP Settings for SSL on port 465
            $this->mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
            $this->mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // SSL encryption
            $this->mail->Port       = 465;  // SSL port

            // Sender Details
            $this->mail->setFrom(
                $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@chatapp.com', 
                $_ENV['SMTP_FROM_NAME'] ?? 'Chat App'
            );

            // Additional SMTP Options for compatibility
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Extended timeout and error handling
            $this->mail->Timeout = 60;  // 1-minute timeout
            $this->mail->SMTPKeepAlive = true;  // Keep connection open

            // Log configuration
            $this->logger->debug('SMTP Configuration Completed', [
                'host' => $this->mail->Host,
                'port' => $this->mail->Port,
                'secure_type' => $this->mail->SMTPSecure
            ]);

        } catch (Exception $e) {
            // Comprehensive error logging
            $this->logger->error('SMTP Configuration Failed', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception("SMTP Configuration Error: " . $e->getMessage());
        }
    }

    private function getCountrySpecificSMTPConfig($countryCode) {
        // Country-specific SMTP configurations
        $countryConfigs = [
            'IN' => [
                'host' => $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com',
                'port' => intval($_ENV['SMTP_PORT'] ?? 465),
                'username' => $_ENV['SMTP_USERNAME'] ?? '',
                'password' => $_ENV['SMTP_PASSWORD'] ?? '',
                'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@chatapp.com',
                'from_name' => $_ENV['SMTP_FROM_NAME'] ?? 'ChatApp India'
            ],
            // Add more country configurations as needed
            'default' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => '',
                'password' => '',
                'from_email' => 'noreply@chatapp.com',
                'from_name' => 'ChatApp'
            ]
        ];

        return $countryConfigs[$countryCode] ?? $countryConfigs['default'];
    }

    public function sendEmail($to, $subject, $body, $isHtml = true) {
        try {
            // Validate inputs
            if (empty($to) || empty($subject) || empty($body)) {
                throw new Exception("Invalid email or content");
            }

            // Reset mail instance
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();

            // Configure email with localized details
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->isHTML($isHtml);
            $this->mail->Body = $body;

            // Logging before sending
            $this->logger->debug('Attempting Email Send', [
                'to' => $to,
                'subject' => $subject
            ]);

            // Send email with extended timeout
            $this->mail->Timeout = 120;  // 2-minute timeout
            $result = $this->mail->send();

            // Log successful email
            $this->logger->info('Email Sent', [
                'to' => $to,
                'result' => $result
            ]);

            return $result;
        } catch (Exception $e) {
            // Comprehensive error logging
            $this->logger->error('Email Send Failed', [
                'to' => $to,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'smtp_error' => $this->mail->ErrorInfo
            ]);

            // Throw detailed exception
            throw new Exception("Email Send Error: " . $e->getMessage() . 
                " (SMTP Error: " . $this->mail->ErrorInfo . ")");
        }
    }

    public function sendPasswordResetEmail($email, $resetLink) {
        try {
            // Validate inputs
            if (empty($email) || empty($resetLink)) {
                throw new Exception("Invalid email or reset link");
            }

            // Reset mail instance
            $this->mail->clearAllRecipients();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();

            // Configure email
            $this->mail->addAddress($email);
            $this->mail->Subject = 'Password Reset Request for Chat App';
            $this->mail->isHTML(true);
            
            // Detailed email body
            $this->mail->Body = $this->getPasswordResetEmailTemplate($resetLink);
            
            // Logging before sending
            $this->logger->debug('Attempting Password Reset Email', [
                'to' => $email,
                'reset_link' => $resetLink
            ]);

            // Send email with extended error handling
            try {
                $result = $this->mail->send();
                
                // Log successful email
                $this->logger->info('Password Reset Email Sent', [
                    'to' => $email,
                    'result' => $result
                ]);

                return $result;
            } catch (Exception $sendException) {
                // Detailed send error logging
                $this->logger->error('Password Reset Email Send Failed', [
                    'to' => $email,
                    'error_message' => $sendException->getMessage(),
                    'smtp_error' => $this->mail->ErrorInfo,
                    'reset_link' => $resetLink
                ]);

                // Additional diagnostic information
                $this->logSMTPDiagnostics();

                throw new Exception("Email Send Error: " . $sendException->getMessage() . 
                    " (SMTP Error: " . $this->mail->ErrorInfo . ")");
            }
        } catch (Exception $e) {
            // Catch-all error handling
            $this->logger->error('Password Reset Email Process Failed', [
                'to' => $email,
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getPasswordResetEmailTemplate($resetLink) {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Password Reset Request</h2>
        <p>You have requested to reset your password. Click the link below to reset:</p>
        <p><a href="{$resetLink}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
        <p>If you did not request this, please ignore this email or contact support if you have concerns.</p>
        <small>This link will expire in 1 hour.</small>
    </div>
</body>
</html>
HTML;
    }

    public function sendWelcomeEmail($email, $username) {
        try {
            // Validate inputs
            if (empty($email) || empty($username)) {
                throw new Exception("Invalid email or username");
            }

            // Reset mail instance
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();

            // Configure email
            $this->mail->addAddress($email);
            $this->mail->Subject = 'Welcome to Chat App!';
            $this->mail->isHTML(true);
            $this->mail->Body = $this->getWelcomeEmailTemplate($username);

            // Logging before sending
            $this->logger->debug('Attempting Welcome Email', [
                'to' => $email,
                'username' => $username
            ]);

            // Send email with extended timeout
            $this->mail->Timeout = 120;  // 2-minute timeout
            $result = $this->mail->send();

            // Log successful email
            $this->logger->info('Welcome Email Sent', [
                'to' => $email,
                'result' => $result
            ]);

            return $result;
        } catch (Exception $e) {
            // Comprehensive error logging
            $this->logger->error('Welcome Email Failed', [
                'to' => $email,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'smtp_error' => $this->mail->ErrorInfo
            ]);

            // Throw detailed exception
            throw new Exception("Email Send Error: " . $e->getMessage() . 
                " (SMTP Error: " . $this->mail->ErrorInfo . ")");
        }
    }

    private function getWelcomeEmailTemplate($username) {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Chat App!</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Welcome to Chat App, {$username}!</h2>
        <p>Thank you for joining our community. Start connecting with friends today!</p>
        <p><a href="'.base_url('login').'" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Login Now</a></p>
    </div>
</body>
</html>
HTML;
    }

    private function logSMTPDiagnostics() {
        // Log additional SMTP diagnostics for troubleshooting
        $diagnostics = [
            'Host' => $this->mail->Host,
            'Port' => $this->mail->Port,
            'SMTPSecure' => $this->mail->SMTPSecure,
            'SMTPAuth' => $this->mail->SMTPAuth ? 'Yes' : 'No',
            'From' => $this->mail->From,
            'FromName' => $this->mail->FromName,
            'Username' => $this->mail->Username ? 'Set' : 'Not Set'
        ];

        $this->logger->debug('SMTP Diagnostics', $diagnostics);
    }
}
