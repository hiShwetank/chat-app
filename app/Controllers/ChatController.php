<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\FriendModel;
use App\Models\ChatModel;

class ChatController {
    private $userModel;
    private $friendModel;
    private $chatModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->friendModel = new FriendModel();
        $this->chatModel = new ChatModel();
    }

    /**
     * Render chat view
     * @return void
     */
    public function index() {
        // Get authenticated user from session
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            exit;
        }

        try {
            // Fetch user details
            $userDetails = $this->userModel->getUserById($user['id']);
            
            // Fetch friends
            $friends = $this->friendModel->getUserFriends($user['id']);
            
            // Fetch recent chats
            $recentChats = $this->chatModel->getRecentChats($user['id']);

            // Render chat view or return JSON
            header('Content-Type: text/html');
            include dirname(__DIR__) . '/Views/chat.php';

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading chat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send a chat message
     * @return void
     */
    public function sendMessage() {
        // Get authenticated user from session
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            exit;
        }

        try {
            // Get message data
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate message
            if (!isset($data['recipient_id']) || !isset($data['message'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid message data'
                ]);
                exit;
            }

            // Send message
            $messageId = $this->chatModel->sendMessage(
                $user['id'], 
                $data['recipient_id'], 
                $data['message']
            );

            echo json_encode([
                'success' => true,
                'message_id' => $messageId
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get chat messages with a specific friend
     * @param int $friendId
     * @return void
     */
    public function getChatMessages($friendId) {
        // Get authenticated user from session
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            exit;
        }

        try {
            // Fetch chat messages
            $messages = $this->chatModel->getChatMessages(
                $user['id'], 
                $friendId
            );

            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching messages: ' . $e->getMessage()
            ]);
        }
    }
}
