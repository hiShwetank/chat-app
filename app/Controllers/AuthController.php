<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Services\DatabaseService;
use App\Services\EmailService;
use Exception;

class AuthController {
    private $userModel;
    private $emailService;

    public function __construct() {
        $db = DatabaseService::getConnection();
        $this->userModel = new UserModel($db);
        $this->emailService = new EmailService();
    }

    // User Registration
    public function register($data) {
        try {
            // Validate registration data
            $this->validateRegistrationData($data);

            // Attempt registration
            $result = $this->userModel->register(
                $data['username'], 
                $data['email'], 
                $data['password']
            );

            // Send welcome email
            $this->emailService->sendWelcomeEmail($data['email'], $data['username']);

            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // User Login
    public function login($data) {
        try {
            // Validate login data
            $this->validateLoginData($data);

            // Attempt login
            $user = $this->userModel->login(
                $data['email'], 
                $data['password']
            );

            // Generate authentication token
            $token = $this->generateAuthToken($user);

            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Forgot Password
    public function forgotPassword($data) {
        try {
            // Validate email
            $this->validateEmail($data['email']);

            // Generate reset token
            $resetToken = $this->userModel->generatePasswordResetToken($data['email']);

            // Send password reset email
            $resetLink = $this->generateResetLink($data['email'], $resetToken);
            $this->emailService->sendPasswordResetEmail($data['email'], $resetLink);

            return [
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Reset Password
    public function resetPassword($data) {
        try {
            // Validate reset data
            $this->validateResetPasswordData($data);

            // Reset password
            $result = $this->userModel->resetPassword(
                $data['email'], 
                $data['token'], 
                $data['new_password']
            );

            return [
                'success' => true,
                'message' => 'Password reset successful'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Input Validation Methods
    private function validateRegistrationData($data) {
        if (empty($data['username']) || strlen($data['username']) < 3) {
            throw new Exception("Username must be at least 3 characters long");
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
    }

    private function validateLoginData($data) {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        if (empty($data['password'])) {
            throw new Exception("Password is required");
        }
    }

    private function validateEmail($email) {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }
    }

    private function validateResetPasswordData($data) {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        if (empty($data['token'])) {
            throw new Exception("Reset token is required");
        }

        if (empty($data['new_password']) || strlen($data['new_password']) < 8) {
            throw new Exception("New password must be at least 8 characters long");
        }
    }

    // Token Generation
    private function generateAuthToken($user) {
        // In a real-world scenario, use JWT or another secure token mechanism
        return base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'timestamp' => time()
        ]));
    }

    // Generate Password Reset Link
    private function generateResetLink($email, $token) {
        // In a real application, this would be a full URL to your reset page
        return "http://localhost:8000/reset-password?email=" . urlencode($email) . "&token=" . urlencode($token);
    }
}
