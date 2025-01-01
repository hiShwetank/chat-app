<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error log file
ini_set('error_log', dirname(__DIR__) . '/error.log');

// Custom autoloader
require_once __DIR__ . '/../config/autoload.php';

use Dotenv\Dotenv;
use App\Services\DatabaseService;
use App\Models\UserModel;
use App\Middleware\AuthMiddleware;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Start session
session_start();

// Load .env file
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Initialize Database
try {
    // Run migrations
    DatabaseService::runMigrations();

    // Get PDO connection
    $db = DatabaseService::getConnection();

    // Initialize models and middleware
    $userModel = new UserModel($db);
    $authMiddleware = new AuthMiddleware($db);

    // Simple routing
    $request = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    // Remove query parameters from request
    $request = strtok($request, '?');

    // Initialize Authentication Middleware
    $authMiddleware = new \App\Middleware\AuthMiddleware($db);

    // Determine if authentication is required based on route
    $authenticatedUser = null;
    try {
        // Allow unauthorized routes for reset password, login, etc.
        $authenticatedUser = $authMiddleware->handle(
            $method, 
            $request, 
            // Allow unauthorized access for specific routes
            in_array($request, [
                '/reset-password', 
                '/login', 
                '/register', 
                '/forgot-password'
            ])
        );
    } catch (Exception $e) {
        // Log authentication errors
        error_log("Authentication Error: " . $e->getMessage());
        
        // Redirect to login for authentication failures
        if (strpos($request, '/reset-password') !== 0) {
            header('Location: /login?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    switch ($request) {
        case '/':
        case '':
            // If user is already logged in, redirect to chat
            if ($authenticatedUser) {
                header('Location: /chat');
                exit;
            }
            include '../app/Views/auth.php';
            break;
        case '/login':
            if ($method === 'GET') {
                // Check for error message
                $errorMessage = $_GET['error'] ?? null;
                
                // If already logged in, redirect to chat
                if ($authenticatedUser) {
                    header('Location: /chat');
                    exit;
                }
                
                // Include login view with optional error
                include '../app/Views/auth.php';
            } elseif ($method === 'POST') {
                // Parse JSON input
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Invalid request data'
                    ]);
                    exit;
                }
                
                try {
                    $result = $userModel->login($data['email'], $data['password']);
                    
                    // Set authentication cookie
                    $jwt = $result['token'];
                    setcookie('auth_token', $jwt, time() + 3600, '/', '', true, true);
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Login successful',
                        'token' => $jwt,
                        'user' => $result
                    ]);
                } catch (Exception $e) {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false, 
                        'message' => $e->getMessage()
                    ]);
                }
            }
            break;
        case '/register':
            if ($method === 'GET') {
                include '../app/Views/auth.php';
            } elseif ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                try {
                    $result = $userModel->register(
                        $data['username'], 
                        $data['email'], 
                        $data['password']
                    );
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Registration successful'
                    ]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => $e->getMessage()
                    ]);
                }
            }
            break;
        case '/chat':
            // Ensure user is authenticated
            if (!$authenticatedUser) {
                header('Location: /login');
                exit;
            }
            
            // Fetch user details and friends
            $userDetails = $userModel->getUserById($authenticatedUser['id']);
            $friends = $userModel->getUserFriends($authenticatedUser['id']);
            $friendRequests = $userModel->getPendingFriendRequests($authenticatedUser['id']);

            include '../app/Views/chat.php';
            break;
        case '/profile':
            if ($method === 'GET') {
                // Authenticate user
                $user = $authMiddleware->handle($method, $request);
                
                // Include profile view
                include '../app/Views/profile.php';
            } elseif ($method === 'POST') {
                // Authenticate user
                $user = $authMiddleware->handle($method, $request);

                // Handle profile update or picture upload
                $data = $_POST;
                $files = $_FILES;

                try {
                    // Handle profile picture upload
                    if (isset($files['profile_picture'])) {
                        $pictureName = $authMiddleware->processProfilePicture(
                            $files['profile_picture']['tmp_name'], 
                            $user['id']
                        );
                        $data['profile_picture'] = $pictureName;
                    }

                    // Update profile
                    $updatedUser = $userModel->updateProfile($user['id'], $data);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Profile updated successfully',
                        'user' => $updatedUser
                    ]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
            }
            break;
        case '/forgot-password':
            if ($method === 'GET') {
                include '../app/Views/auth.php';
            } elseif ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                try {
                    $resetToken = $userModel->generatePasswordResetToken($data['email']);
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Password reset link sent',
                        'token' => $resetToken
                    ]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => $e->getMessage()
                    ]);
                }
            }
            break;
        case '/reset-password':
            if ($method === 'GET') {
                // Validate reset token
                $token = $_GET['token'] ?? null;
                
                try {
                    // Verify token without logging in
                    $userModel->verifyPasswordResetToken($token);
                    
                    // Include reset password view
                    include '../app/Views/reset_password.php';
                } catch (Exception $e) {
                    // Invalid or expired token
                    header('Location: /login?error=' . urlencode($e->getMessage()));
                    exit;
                }
            } elseif ($method === 'POST') {
                // Process password reset
                $data = json_decode(file_get_contents('php://input'), true);
                
                try {
                    $result = $userModel->resetPassword(
                        $data['token'], 
                        $data['new_password']
                    );
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Password reset successful. Please log in.'
                    ]);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => $e->getMessage()
                    ]);
                }
            }
            break;
        default:
            http_response_code(404);
            include '../app/Views/404.php';
            break;
    }
} catch (Exception $e) {
    // Log error and show user-friendly error page
    error_log("Application Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred'
    ]);
    exit;
}
