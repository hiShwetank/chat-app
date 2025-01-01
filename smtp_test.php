<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function testSMTPConnection() {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Enable verbose debug output
        $mail->isSMTP();  // Send using SMTP

        // Hostinger SMTP Configuration
        $mail->Host       = $_ENV['SMTP_HOST'];  // SMTP server
        $mail->SMTPAuth   = true;  // Enable SMTP authentication
        $mail->Username   = $_ENV['SMTP_USERNAME'];  // SMTP username
        $mail->Password   = $_ENV['SMTP_PASSWORD'];  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
        $mail->Port       = 587;  // TCP port to connect to (587 for TLS)

        // Additional debugging options
        $mail->Debugoutput = function($str, $level) {
            echo "Debug level $level: $str\n";
        };

        // Attempt connection
        $smtp = new SMTP();
        $smtp->setTimeout(30);  // 30-second timeout
        
        // Connect to SMTP server
        echo "Attempting to connect to SMTP server...\n";
        $connected = $smtp->connect($_ENV['SMTP_HOST'], $_ENV['SMTP_PORT']);
        
        if (!$connected) {
            echo "SMTP Connection Failed.\n";
            echo "Error: " . $smtp->getError()['error'] . "\n";
            return false;
        }

        // Authenticate
        echo "Attempting to authenticate...\n";
        $authenticated = $smtp->authenticate(
            $_ENV['SMTP_USERNAME'], 
            $_ENV['SMTP_PASSWORD']
        );

        if (!$authenticated) {
            echo "SMTP Authentication Failed.\n";
            echo "Error: " . $smtp->getError()['error'] . "\n";
            return false;
        }

        echo "SMTP Connection and Authentication Successful!\n";

        // Attempt to send a test email
        $mail->setFrom($_ENV['SMTP_USERNAME'], 'SMTP Test');
        $mail->addAddress($_ENV['SMTP_USERNAME']);  // Send to self
        $mail->Subject = 'SMTP Connection Test';
        $mail->Body    = 'This is a test email to verify SMTP configuration.';

        // Send email
        echo "Attempting to send test email...\n";
        $emailSent = $mail->send();

        if ($emailSent) {
            echo "Test email sent successfully!\n";
        } else {
            echo "Failed to send test email.\n";
            echo "Error: " . $mail->ErrorInfo . "\n";
        }

        // Close connection
        $smtp->quit();

        return true;

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run the test
echo "Starting SMTP Configuration Test...\n";
$result = testSMTPConnection();
echo $result ? "Test Completed Successfully" : "Test Failed";
