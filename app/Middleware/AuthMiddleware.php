<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\UserModel;
use PDO;

class AuthMiddleware {
    private $secretKey;
    private $db;
    private $userModel;

    public function __construct(PDO $db) {
        // Get secret key from environment
        $this->secretKey = $_ENV['APP_KEY'] ?? 'default_secret_key';
        $this->db = $db;
        $this->userModel = new UserModel($db);
    }

    public function authenticate() {
        // Reset session if needed
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check for token
        $token = $this->getToken();

        if (!$token) {
            $this->redirectToLogin("No authentication token found.");
        }

        try {
            // Decode and validate JWT
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            
            // Verify user exists and is active
            $user = $this->userModel->getUserById($decoded->user_id);
            
            if (!$user) {
                $this->redirectToLogin("User account not found.");
            }

            // Validate token claims
            $now = time();
            if ($decoded->exp < $now) {
                $this->redirectToLogin("Session expired. Please log in again.");
            }

            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['status'] = $user['status'];
            $_SESSION['auth_token'] = $token;

            return $user;
        } catch (ExpiredException $e) {
            // Token expired
            $this->redirectToLogin("Session expired. Please log in again.");
        } catch (\Exception $e) {
            // Invalid token or other authentication errors
            $this->redirectToLogin("Invalid authentication. Please log in.");
        }
    }

    public function redirectToLogin($message = null) {
        // Clear any existing session data
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        // Delete authentication cookie
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/', '', false, true);
        }

        // Prepare error message
        $errorParam = $message ? '?error=' . urlencode($message) : '';

        // Redirect to login page
        header('Location: /login' . $errorParam);
        exit;
    }

    private function getToken() {
        // Check multiple sources for token
        $token = null;

        // 1. Check cookie
        if (isset($_COOKIE['auth_token'])) {
            $token = $_COOKIE['auth_token'];
        }

        // 2. Check Authorization header
        if (!$token && function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $token = str_replace('Bearer ', '', $headers['Authorization']);
            }
        }

        // 3. Check session
        if (!$token && isset($_SESSION['auth_token'])) {
            $token = $_SESSION['auth_token'];
        }

        // 4. Check GET/POST parameters (for API or testing)
        if (!$token && isset($_GET['token'])) {
            $token = $_GET['token'];
        }

        return $token;
    }

    // Middleware to check if user is already logged in
    public function preventLoggedInAccess() {
        $token = $this->getToken();

        if ($token) {
            try {
                // If token is valid, redirect to profile
                JWT::decode($token, new Key($this->secretKey, 'HS256'));
                header('Location: /profile');
                exit;
            } catch (\Exception $e) {
                // Invalid token, continue to login page
                return;
            }
        }
    }

    // Generate secure profile picture upload path
    public function generateProfilePicturePath($userId) {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/profiles/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = $userId . '_' . uniqid() . '.jpg';
        return [
            'directory' => $uploadDir,
            'filename' => $filename,
            'full_path' => $uploadDir . $filename
        ];
    }

    // Validate and resize profile picture
    public function processProfilePicture($tempFile, $userId) {
        // Allowed mime types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        // Get file info
        $fileInfo = getimagesize($tempFile);
        $mimeType = $fileInfo['mime'];
        $fileSize = filesize($tempFile);

        // Validate file
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception("Invalid file type. Only JPEG, PNG, and GIF are allowed.");
        }

        if ($fileSize > $maxFileSize) {
            throw new \Exception("File too large. Maximum size is 5MB.");
        }

        // Generate upload path
        $uploadPath = $this->generateProfilePicturePath($userId);

        // Resize and convert to JPEG
        $this->resizeImage($tempFile, $uploadPath['full_path'], 500, 500);

        return $uploadPath['filename'];
    }

    private function resizeImage($source, $destination, $maxWidth, $maxHeight) {
        // Get original image info
        $sourceImage = imagecreatefromstring(file_get_contents($source));
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Enable alpha blending
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        // Resize
        imagecopyresampled(
            $newImage, $sourceImage, 
            0, 0, 0, 0, 
            $newWidth, $newHeight, 
            $width, $height
        );

        // Save as JPEG
        imagejpeg($newImage, $destination, 85);

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
    }
}