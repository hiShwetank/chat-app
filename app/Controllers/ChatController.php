<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\FriendModel;
use App\Middleware\AuthMiddleware;

class ChatController {
    private $userModel;
    private $friendModel;
    private $authMiddleware;

    public function __construct($dbPath) {
        $this->userModel = new UserModel($dbPath);
        $this->friendModel = new FriendModel($dbPath);
        $this->authMiddleware = new AuthMiddleware();
    }

    public function loadChatPage() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get user details
        $userDetails = $this->userModel->getUserById($user->user_id);

        // Get friend list
        $friends = $this->friendModel->getFriendList($user->user_id);

        // Get friend requests
        $friendRequests = $this->friendModel->getFriendRequests($user->user_id);

        // Render chat page with user and friends data
        include '../app/Views/chat.php';
    }

    public function sendMessage() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get message details
        $receiverId = $_POST['receiver_id'] ?? null;
        $message = $_POST['message'] ?? null;

        if (!$receiverId || !$message) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid message data']);
            return;
        }

        // Save message to database (implement message model)
        $result = $this->saveMessage($user->user_id, $receiverId, $message);

        if ($result) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Message sent']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send message']);
        }
    }

    private function saveMessage($senderId, $receiverId, $message) {
        // Implement message saving logic
        // This would typically involve inserting into a messages table
        $stmt = $this->userModel->getDatabase()->prepare('
            INSERT INTO messages (sender_id, receiver_id, content, created_at) 
            VALUES (:sender_id, :receiver_id, :content, CURRENT_TIMESTAMP)
        ');
        $stmt->bindValue(':sender_id', $senderId, SQLITE3_INTEGER);
        $stmt->bindValue(':receiver_id', $receiverId, SQLITE3_INTEGER);
        $stmt->bindValue(':content', $message, SQLITE3_TEXT);

        return $stmt->execute();
    }

    public function getChatHistory() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get chat partner ID
        $partnerId = $_GET['partner_id'] ?? null;

        if (!$partnerId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid partner ID']);
            return;
        }

        // Fetch chat history
        $history = $this->fetchChatHistory($user->user_id, $partnerId);

        echo json_encode($history);
    }

    private function fetchChatHistory($userId, $partnerId) {
        // Implement chat history retrieval
        $stmt = $this->userModel->getDatabase()->prepare('
            SELECT * FROM messages 
            WHERE (sender_id = :user_id AND receiver_id = :partner_id) 
               OR (sender_id = :partner_id AND receiver_id = :user_id)
            ORDER BY created_at ASC
        ');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':partner_id', $partnerId, SQLITE3_INTEGER);

        $result = $stmt->execute();
        $messages = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $messages[] = $row;
        }

        return $messages;
    }
}
