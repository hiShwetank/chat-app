<?php
namespace App\Controllers;

use App\Models\FriendModel;
use App\Middleware\AuthMiddleware;

class FriendController {
    private $friendModel;
    private $authMiddleware;

    public function __construct($dbPath) {
        $this->friendModel = new FriendModel($dbPath);
        $this->authMiddleware = new AuthMiddleware();
    }

    public function sendFriendRequest() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get friend username from request
        $receiverUsername = $_POST['username'] ?? null;

        if (!$receiverUsername) {
            http_response_code(400);
            echo json_encode(['error' => 'Username is required']);
            return;
        }

        // Send friend request
        $result = $this->friendModel->sendFriendRequest($user->user_id, $receiverUsername);

        if (isset($result['success'])) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    public function getFriendRequests() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get friend requests
        $requests = $this->friendModel->getFriendRequests($user->user_id);

        echo json_encode($requests);
    }

    public function acceptFriendRequest() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get request ID
        $requestId = $_POST['request_id'] ?? null;

        if (!$requestId) {
            http_response_code(400);
            echo json_encode(['error' => 'Request ID is required']);
            return;
        }

        // Accept friend request
        $result = $this->friendModel->acceptFriendRequest($requestId, $user->user_id);

        if (isset($result['success'])) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    public function getFriendList() {
        // Authenticate user
        $user = $this->authMiddleware->authenticate();

        // Get friend list
        $friends = $this->friendModel->getFriendList($user->user_id);

        echo json_encode($friends);
    }
}
