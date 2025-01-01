<?php
namespace App\Controllers;

use App\Models\InviteModel;
use App\Models\UserModel;
use App\Services\EmailService;
use Firebase\JWT\JWT;

class InviteController {
    private $inviteModel;
    private $userModel;
    private $emailService;

    public function __construct() {
        $this->inviteModel = new InviteModel();
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
    }

    /**
     * Generate a unique invite link
     * @return void
     */
    public function generateInviteLink() {
        try {
            // Get current user from session
            $currentUser = $_SESSION['user'] ?? null;
            
            if (!$currentUser) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]);
                return;
            }

            // Generate unique invite token
            $token = bin2hex(random_bytes(16));
            
            // Prepare invite data
            $inviteData = [
                'token' => $token,
                'user_id' => $currentUser['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'status' => 'active'
            ];

            // Store invite in database
            $this->inviteModel->createInvite($inviteData);

            // Generate full invite link
            $inviteLink = getenv('APP_URL') . "/invite/{$token}";

            // Return invite link
            echo json_encode([
                'success' => true,
                'link' => $inviteLink,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate invite link: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send invite via email
     * @return void
     */
    public function sendEmailInvite() {
        try {
            // Get current user from session
            $currentUser = $_SESSION['user'] ?? null;
            
            if (!$currentUser) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]);
                return;
            }

            // Get request data
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? null;
            $message = $data['message'] ?? '';

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email address'
                ]);
                return;
            }

            // Check if user already exists
            $existingUser = $this->userModel->getUserByEmail($email);
            if ($existingUser) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'User with this email already exists'
                ]);
                return;
            }

            // Generate unique invite token
            $token = bin2hex(random_bytes(16));
            
            // Prepare invite data
            $inviteData = [
                'token' => $token,
                'user_id' => $currentUser['id'],
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'status' => 'active'
            ];

            // Store invite in database
            $this->inviteModel->createInvite($inviteData);

            // Generate full invite link
            $inviteLink = getenv('APP_URL') . "/invite/{$token}";

            // Prepare email content
            $emailContent = $this->prepareInviteEmailContent(
                $currentUser['username'], 
                $inviteLink, 
                $message
            );

            // Send invite email
            $this->emailService->sendEmail(
                $email, 
                'You\'ve been invited to join our chat app!', 
                $emailContent
            );

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Invite sent successfully'
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to send invite: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Redeem an invite link
     * @param string $token
     * @return void
     */
    public function redeemInviteLink($token) {
        try {
            // Find invite by token
            $invite = $this->inviteModel->getInviteByToken($token);
            
            if (!$invite || $invite['status'] !== 'active') {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid or expired invite link'
                ]);
                return;
            }

            // Check if invite has expired
            $expiresAt = strtotime($invite['expires_at']);
            if ($expiresAt < time()) {
                // Update invite status to expired
                $this->inviteModel->updateInviteStatus($token, 'expired');
                
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invite link has expired'
                ]);
                return;
            }

            // Redirect to registration with invite token
            header("Location: /register?invite_token={$token}");
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to process invite: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Prepare invite email content
     * @param string $inviterName
     * @param string $inviteLink
     * @param string $personalMessage
     * @return string
     */
    private function prepareInviteEmailContent($inviterName, $inviteLink, $personalMessage = '') {
        $content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2>You've been invited to join our Chat App!</h2>
            <p>Your friend <strong>{$inviterName}</strong> has invited you to join our chat application.</p>
            
            " . ($personalMessage ? "<p><em>Personal message:</em> {$personalMessage}</p>" : "") . "
            
            <p>To accept the invitation, click the link below:</p>
            
            <a href='{$inviteLink}' style='
                display: inline-block; 
                background-color: #4CAF50; 
                color: white; 
                padding: 10px 20px; 
                text-decoration: none; 
                border-radius: 5px;
            '>Accept Invitation</a>
            
            <p style='color: #888; font-size: 0.9em;'>
                This invite link will expire in 7 days. 
                If you didn't request this invite, you can safely ignore this email.
            </p>
        </div>";
        
        return $content;
    }
}
