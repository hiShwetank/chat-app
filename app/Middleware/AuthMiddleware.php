<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\UserModel;

class AuthMiddleware {
    private $unprotectedRoutes = [
        '/login', 
        '/register', 
        '/reset-password', 
        '/forgot-password',
        '/invite/generate-link',
        '/invite/email'
    ];

    /**
     * Handle authentication for incoming requests
     * @param string $method
     * @param string $uri
     * @param bool $requireAuth
     * @return array|null
     * @throws \Exception
     */
    public function handle($method, $uri, $requireAuth = true) {
        // Remove query parameters from URI
        $uri = strtok($uri, '?');
        
        // Skip authentication for unprotected routes and OPTIONS requests
        if (
            $method === 'OPTIONS' || 
            $this->isUnprotectedRoute($uri) || 
            $this->isPublicInviteRoute($uri) ||
            !$requireAuth
        ) {
            return null;
        }

        // Check for authentication token
        $token = $this->getAuthToken();
        
        if (!$token) {
            $this->unauthorized('No authentication token provided');
        }

        try {
            // Validate JWT token using new method
            $decoded = JWT::decode(
                $token, 
                new Key(getenv('JWT_SECRET'), 'HS256')
            );
            
            // Verify user exists
            $userModel = new UserModel();
            $user = $userModel->getUserById($decoded->user_id);
            
            if (!$user) {
                $this->unauthorized('User not found');
            }

            // Store user info in session
            $_SESSION['user'] = $user;

            return $user;

        } catch (ExpiredException $e) {
            $this->unauthorized('Token has expired');
        } catch (\Exception $e) {
            $this->unauthorized('Invalid authentication token');
        }
    }

    /**
     * Check if route is unprotected
     * @param string $uri
     * @return bool
     */
    private function isUnprotectedRoute($uri) {
        foreach ($this->unprotectedRoutes as $route) {
            if (strpos($uri, $route) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if route is a public invite route
     * @param string $uri
     * @return bool
     */
    private function isPublicInviteRoute($uri) {
        return preg_match('/^\/invite\/[a-f0-9]+$/', $uri) === 1;
    }

    /**
     * Retrieve authentication token from headers or cookies
     * @return string|null
     */
    private function getAuthToken() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        // Check cookies
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // Check session
        if (isset($_SESSION['auth_token'])) {
            return $_SESSION['auth_token'];
        }

        return null;
    }

    /**
     * Send unauthorized response
     * @param string $message
     * @throws \Exception
     */
    private function unauthorized($message = 'Unauthorized') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}
