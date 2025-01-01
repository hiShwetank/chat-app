<?php
namespace App\Models;

use SQLite3;

class FriendModel {
    private $db;

    public function __construct($dbPath) {
        $this->db = new SQLite3($dbPath);
    }

    public function sendFriendRequest($senderId, $receiverUsername) {
        // Find receiver by username
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username');
        $stmt->bindValue(':username', $receiverUsername, SQLITE3_TEXT);
        $result = $stmt->execute();
        $receiver = $result->fetchArray(SQLITE3_ASSOC);

        if (!$receiver) {
            return ['error' => 'User not found'];
        }

        $receiverId = $receiver['id'];

        // Check if request already exists
        $checkStmt = $this->db->prepare('
            SELECT * FROM friends 
            WHERE (user_id = :sender_id AND friend_id = :receiver_id) 
            OR (user_id = :receiver_id AND friend_id = :sender_id)
        ');
        $checkStmt->bindValue(':sender_id', $senderId, SQLITE3_INTEGER);
        $checkStmt->bindValue(':receiver_id', $receiverId, SQLITE3_INTEGER);
        $existingRequest = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($existingRequest) {
            return ['error' => 'Friend request already exists'];
        }

        // Insert friend request
        $insertStmt = $this->db->prepare('
            INSERT INTO friends (user_id, friend_id, status) 
            VALUES (:sender_id, :receiver_id, "pending")
        ');
        $insertStmt->bindValue(':sender_id', $senderId, SQLITE3_INTEGER);
        $insertStmt->bindValue(':receiver_id', $receiverId, SQLITE3_INTEGER);

        if ($insertStmt->execute()) {
            return [
                'success' => true, 
                'message' => 'Friend request sent',
                'receiver_id' => $receiverId
            ];
        }

        return ['error' => 'Failed to send friend request'];
    }

    public function getFriendRequests($userId) {
        $stmt = $this->db->prepare('
            SELECT f.id, u.username, u.email, f.status 
            FROM friends f
            JOIN users u ON f.user_id = u.id
            WHERE f.friend_id = :user_id AND f.status = "pending"
        ');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $requests = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $requests[] = $row;
        }

        return $requests;
    }

    public function acceptFriendRequest($requestId, $userId) {
        $stmt = $this->db->prepare('
            UPDATE friends 
            SET status = "accepted" 
            WHERE id = :request_id AND friend_id = :user_id
        ');
        $stmt->bindValue(':request_id', $requestId, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Friend request accepted'];
        }

        return ['error' => 'Failed to accept friend request'];
    }

    public function getFriendList($userId) {
        $stmt = $this->db->prepare('
            SELECT u.id, u.username, u.email, u.status 
            FROM friends f
            JOIN users u ON 
                (f.user_id = :user_id AND u.id = f.friend_id) 
                OR 
                (f.friend_id = :user_id AND u.id = f.user_id)
            WHERE f.status = "accepted"
        ');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $friends = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $friends[] = $row;
        }

        return $friends;
    }
}
