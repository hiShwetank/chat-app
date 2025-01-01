<?php
namespace App\Models;

use PDO;
use Exception;
use Firebase\JWT\JWT;

class UserModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register($username, $email, $password) {
        // Validate input
        $this->validateRegistrationInput($username, $email, $password);

        // Check if user already exists
        $checkUserQuery = "SELECT * FROM users WHERE email = :email OR username = :username";
        $checkStmt = $this->db->prepare($checkUserQuery);
        $checkStmt->execute([
            ':email' => $email,
            ':username' => $username
        ]);

        if ($checkStmt->fetch()) {
            throw new Exception("User with this email or username already exists");
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $insertQuery = "
            INSERT INTO users (username, email, password, status) 
            VALUES (:username, :email, :password, 'offline')
        ";
        
        try {
            $stmt = $this->db->prepare($insertQuery);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            // Return the newly created user's ID
            return [
                'id' => $this->db->lastInsertId(),
                'username' => $username,
                'email' => $email
            ];
        } catch (Exception $e) {
            error_log("Registration Error: " . $e->getMessage());
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }

    public function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }

        // Find user by email
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user and password
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid email or password");
        }

        // Update user status and last login
        $updateQuery = "
            UPDATE users 
            SET 
                status = 'online', 
                last_login = :last_login 
            WHERE id = :id
        ";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->execute([
            ':last_login' => date('Y-m-d H:i:s'),
            ':id' => $user['id']
        ]);

        // Remove sensitive information
        unset($user['password']);
        $user['token'] = $this->generateToken($user);
        return $user;
    }

    public function generatePasswordResetToken($email) {
        // Logger for tracking
        $logger = new \App\Services\LoggerService('user');

        // Validate email
        if (empty($email)) {
            $logger->error('Password Reset Token Generation Failed', [
                'reason' => 'Empty email',
                'input_email' => $email
            ]);
            throw new Exception("Email is required");
        }

        // Find user by email
        $query = "SELECT id, username FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $logger->error('Password Reset Token Generation Failed', [
                'reason' => 'User not found',
                'input_email' => $email
            ]);
            throw new Exception("No account found with this email address.");
        }

        // Generate secure reset token
        $resetToken = bin2hex(random_bytes(32));
        $expiryTime = time() + 3600; // Token valid for 1 hour

        // Store reset token and expiry
        $updateQuery = "
            UPDATE users 
            SET reset_token = :reset_token, 
                reset_token_expiry = :expiry_time 
            WHERE id = :user_id
        ";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->execute([
            ':reset_token' => $resetToken,
            ':expiry_time' => $expiryTime,
            ':user_id' => $user['id']
        ]);

        // Construct reset link using base_url function
        $resetLink = base_url("reset-password?token={$resetToken}");

        // Send reset email
        try {
            $emailService = new \App\Services\EmailService();
            $emailService->sendPasswordResetEmail($email, $resetLink);

            // Log successful token generation
            $logger->info('Password Reset Token Generated', [
                'user_id' => $user['id'],
                'email' => $email,
                'token_expiry' => date('Y-m-d H:i:s', $expiryTime)
            ]);
        } catch (Exception $e) {
            // Log email sending error
            $logger->error('Password Reset Email Error', [
                'user_id' => $user['id'],
                'email' => $email,
                'error_message' => $e->getMessage()
            ]);
            throw new Exception("Failed to send password reset email. Please try again later.");
        }

        return $resetToken;
    }

    public function verifyPasswordResetToken($token) {
        // Verify reset token
        $query = "
            SELECT id, username, email 
            FROM users 
            WHERE reset_token = :token AND reset_token_expiry > :current_time
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':token' => $token,
            ':current_time' => time()
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("Invalid or expired reset token.");
        }

        return $user;
    }

    public function resetPassword($token, $newPassword) {
        // Verify token first
        $user = $this->verifyPasswordResetToken($token);

        // Validate new password
        $this->validatePassword($newPassword);

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password and clear reset token
        $updateQuery = "
            UPDATE users 
            SET 
                password = :password, 
                reset_token = NULL, 
                reset_token_expiry = NULL 
            WHERE id = :user_id
        ";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->execute([
            ':password' => $hashedPassword,
            ':user_id' => $user['id']
        ]);

        return true;
    }

    private function validateRegistrationInput($username, $email, $password) {
        // Username validation
        if (empty($username) || strlen($username) < 3) {
            throw new Exception("Username must be at least 3 characters long");
        }

        // Email validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Password validation
        if (empty($password) || strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
    }

    private function validatePassword($password) {
        if (empty($password) || strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
    }

    // Get User by ID with more details
    public function getUserById($userId, $includeRelations = true) {
        $query = "SELECT 
            id, 
            username, 
            email, 
            status, 
            created_at,
            profile_picture
            FROM users 
            WHERE id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("User not found");
        }

        if ($includeRelations) {
            // Fetch user's groups
            $user['groups'] = $this->getUserGroups($userId);

            // Fetch user's friends
            $user['friends'] = $this->getUserFriends($userId);
        }

        return $user;
    }

    // Update user profile
    public function updateProfile($userId, $data) {
        $updateFields = [];
        $params = [':user_id' => $userId];

        // Validate and prepare update fields
        if (isset($data['username'])) {
            $updateFields[] = "username = :username";
            $params[':username'] = $data['username'];
        }

        if (isset($data['email'])) {
            $updateFields[] = "email = :email";
            $params[':email'] = $data['email'];
        }

        if (isset($data['profile_picture'])) {
            $updateFields[] = "profile_picture = :profile_picture";
            $params[':profile_picture'] = $data['profile_picture'];
        }

        if (empty($updateFields)) {
            throw new Exception("No fields to update");
        }

        $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $this->getUserById($userId);
    }

    // Get user's groups
    private function getUserGroups($userId) {
        try {
            // First, check if the description column exists
            $checkQuery = "PRAGMA table_info(groups)";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute();
            $columns = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Determine if description column exists
            $hasDescriptionColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'description') {
                    $hasDescriptionColumn = true;
                    break;
                }
            }

            // Construct query based on column existence
            $query = $hasDescriptionColumn 
                ? "SELECT g.id, g.name, g.description, ug.role FROM groups g JOIN user_groups ug ON g.id = ug.group_id WHERE ug.user_id = :user_id"
                : "SELECT g.id, g.name, ug.role FROM groups g JOIN user_groups ug ON g.id = ug.group_id WHERE ug.user_id = :user_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log("Error fetching user groups: " . $e->getMessage());
            
            // Return an empty array if there's an error
            return [];
        }
    }

    // Get user's friends
    public function getUserFriends($userId) {
        $query = "
            SELECT 
                u.id, 
                u.username, 
                u.email, 
                u.status, 
                u.profile_picture,
                f.status as friendship_status
            FROM friends f
            JOIN users u ON 
                (f.user_id = :user_id AND u.id = f.friend_id) OR 
                (f.friend_id = :user_id AND u.id = f.user_id)
            WHERE f.status = 'accepted'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get pending friend requests
    public function getPendingFriendRequests($userId) {
        $query = "
            SELECT 
                u.id, 
                u.username, 
                u.email, 
                u.profile_picture,
                f.id as friend_request_id,
                f.created_at as request_date
            FROM friends f
            JOIN users u ON u.id = f.user_id
            WHERE f.friend_id = :user_id AND f.status = 'pending'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Send friend request
    public function sendFriendRequest($senderId, $recipientUsername) {
        // Find recipient user
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $recipientUsername]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            throw new Exception("User not found");
        }

        // Check if friend request already exists
        $checkQuery = "
            SELECT id FROM friends 
            WHERE (user_id = :sender_id AND friend_id = :recipient_id) OR 
                  (user_id = :recipient_id AND friend_id = :sender_id)
        ";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([
            ':sender_id' => $senderId, 
            ':recipient_id' => $recipient['id']
        ]);

        if ($checkStmt->fetch()) {
            throw new Exception("Friend request already exists");
        }

        // Send friend request
        $insertQuery = "
            INSERT INTO friends (user_id, friend_id, status, created_at) 
            VALUES (:sender_id, :recipient_id, 'pending', CURRENT_TIMESTAMP)
        ";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->execute([
            ':sender_id' => $senderId, 
            ':recipient_id' => $recipient['id']
        ]);

        return true;
    }

    // Accept friend request
    public function acceptFriendRequest($userId, $friendRequestId) {
        $query = "
            UPDATE friends 
            SET status = 'accepted', updated_at = CURRENT_TIMESTAMP
            WHERE id = :friend_request_id AND friend_id = :user_id AND status = 'pending'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':friend_request_id' => $friendRequestId,
            ':user_id' => $userId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Friend request not found or already processed");
        }

        return true;
    }

    // Reject friend request
    public function rejectFriendRequest($userId, $friendRequestId) {
        $query = "
            DELETE FROM friends 
            WHERE id = :friend_request_id AND friend_id = :user_id AND status = 'pending'
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':friend_request_id' => $friendRequestId,
            ':user_id' => $userId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Friend request not found or already processed");
        }

        return true;
    }

    public function generateToken($user) {
        // Ensure secret key is set
        $secretKey = $_ENV['APP_KEY'] ?? 'default_secret_key';
        
        // Token payload
        $payload = [
            'iat' => time(),
            'exp' => time() + 3600, // Token expires in 1 hour
            'user_id' => $user['id'],
            'email' => $user['email']
        ];
        
        // Generate JWT
        return JWT::encode($payload, $secretKey, 'HS256');
    }
}
