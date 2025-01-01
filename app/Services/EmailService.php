<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    private $logger;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->logger = new \App\Services\LoggerService('email');
        $this->configureSMTP();
    }

    private function configureSMTP() {
        try {
            // Reset any previous configuration
            $this->mail->clearAllRecipients();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();

            // SMTP configuration with detailed error logging
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Enable verbose debug output
            $this->mail->isSMTP();

            // SMTP Server Configuration
            $this->mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $this->mail->Port = intval($_ENV['SMTP_PORT'] ?? 587);
            $this->mail->SMTPAuth = true;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            // Credentials
            $this->mail->Username = $_ENV['SMTP_USERNAME'] ?? '';
            $this->mail->Password = $_ENV['SMTP_PASSWORD'] ?? '';

            // Sender Details
            $this->mail->setFrom(
                $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@chatapp.com', 
                $_ENV['SMTP_FROM_NAME'] ?? 'Chat App'
            );

            // Additional SMTP Options
            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Log SMTP configuration details
            $this->logger->debug('SMTP Configuration', [
                'host' => $this->mail->Host,
                'port' => $this->mail->Port,
                'username' => $this->mail->Username ? 'REDACTED' : 'NOT SET',
                'from_email' => $this->mail->From
            ]);

        } catch (Exception $e) {
            $this->logger->error('SMTP Configuration Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to configure email service: " . $e->getMessage());
        }
    }

    public function sendEmail($to, $subject, $body, $isHtml = true) {
        try {
            // Reset mail instance
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->clearCustomHeaders();

            // Configure email
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->isHTML($isHtml);
            $this->mail->Body = $body;

            // Log email details before sending
            $this->logger->debug('Sending Email', [
                'to' => $to,
                'subject' => $subject,
                'is_html' => $isHtml
            ]);

            // Send email
            $result = $this->mail->send();

            // Log successful email
            $this->logger->info("Email sent successfully to $to");

            return $result;
        } catch (Exception $e) {
            // Detailed error logging
            $this->logger->error('Email Send Error', [
                'to' => $to,
                'subject' => $subject,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to send email: " . $e->getMessage());
        }
    }

    public function sendPasswordResetEmail($email, $resetLink) {
        $subject = 'Password Reset Request for Chat App';
        $body = $this->getPasswordResetEmailTemplate($resetLink);

        return $this->sendEmail($email, $subject, $body);
    }

    private function getPasswordResetEmailTemplate($resetLink) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #f4f4f4; padding: 20px; text-align: center;'>
                <h2 style='color: #333;'>Password Reset Request</h2>
                <p style='color: #666;'>You have requested to reset your password. Click the button below to proceed:</p>
                <a href='{$resetLink}' style='
                    display: inline-block; 
                    background-color: #4CAF50; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 5px;
                    margin: 15px 0;
                '>Reset Password</a>
                <p style='color: #999; font-size: 12px;'>This link will expire in 1 hour. If you did not request this, please ignore this email.</p>
            </div>
        </body>
        </html>";
    }

    public function sendWelcomeEmail($email, $username) {
        $subject = 'Welcome to Chat App!';
        $body = $this->getWelcomeEmailTemplate($username);

        return $this->sendEmail($email, $subject, $body);
    }

    private function getWelcomeEmailTemplate($username) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #f4f4f4; padding: 20px; text-align: center;'>
                <h2 style='color: #333;'>Welcome to Chat App, {$username}!</h2>
                <p style='color: #666;'>Thank you for joining our community. Start connecting with friends today!</p>
                <a href='".base_url('login')."' style='
                    display: inline-block; 
                    background-color: #4CAF50; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 5px;
                    margin: 15px 0;
                '>Login Now</a>
            </div>
        </body>
        </html>";
    }
}
