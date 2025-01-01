<?php
namespace Src\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Exception;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $users;

    public function __construct() {
        // Use SplObjectStorage for better performance and memory management
        $this->clients = new \SplObjectStorage();
        $this->users = [];
        
        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    public function onOpen(ConnectionInterface $conn) {
        try {
            $this->clients->attach($conn);
            echo "New connection! ({$conn->resourceId})\n";
        } catch (Exception $e) {
            error_log("Connection Open Error: " . $e->getMessage());
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);

            if (!$data) {
                throw new Exception("Invalid JSON message");
            }

            switch ($data['type'] ?? null) {
                case 'authenticate':
                    $this->authenticateUser($from, $data);
                    break;
                case 'private_message':
                    $this->sendPrivateMessage($from, $data);
                    break;
                case 'group_message':
                    $this->sendGroupMessage($from, $data);
                    break;
                default:
                    throw new Exception("Unknown message type");
            }
        } catch (Exception $e) {
            error_log("Message Handling Error: " . $e->getMessage());
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }

    private function authenticateUser(ConnectionInterface $conn, $data) {
        try {
            // Validate authentication data
            if (!isset($data['user_id'])) {
                throw new Exception("User ID required for authentication");
            }

            $userId = $data['user_id'];
            $this->users[$userId] = $conn;
            $conn->userId = $userId;
            
            // Notify friends about online status
            $this->broadcastUserStatus($userId, 'online');
            
            echo "User $userId authenticated\n";
        } catch (Exception $e) {
            error_log("Authentication Error: " . $e->getMessage());
            $conn->send(json_encode([
                'type' => 'auth_error',
                'message' => $e->getMessage()
            ]));
        }
    }

    private function sendPrivateMessage(ConnectionInterface $from, $data) {
        try {
            $receiverId = $data['receiver_id'] ?? null;
            $message = $data['message'] ?? null;

            if (!$receiverId || !$message) {
                throw new Exception("Invalid private message data");
            }
            
            if (isset($this->users[$receiverId])) {
                $receiverConn = $this->users[$receiverId];
                $receiverConn->send(json_encode([
                    'type' => 'private_message',
                    'sender_id' => $from->userId,
                    'message' => $message
                ]));
            } else {
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => "Receiver not online"
                ]));
            }
        } catch (Exception $e) {
            error_log("Private Message Error: " . $e->getMessage());
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }

    private function sendGroupMessage(ConnectionInterface $from, $data) {
        try {
            $groupId = $data['group_id'] ?? null;
            $message = $data['message'] ?? null;

            if (!$groupId || !$message) {
                throw new Exception("Invalid group message data");
            }
            
            foreach ($this->clients as $client) {
                if ($client !== $from) {
                    $client->send(json_encode([
                        'type' => 'group_message',
                        'group_id' => $groupId,
                        'sender_id' => $from->userId,
                        'message' => $message
                    ]));
                }
            }
        } catch (Exception $e) {
            error_log("Group Message Error: " . $e->getMessage());
            $from->send(json_encode([
                'type' => 'error',
                'message' => $e->getMessage()
            ]));
        }
    }

    private function broadcastUserStatus($userId, $status) {
        try {
            foreach ($this->clients as $client) {
                if (!isset($client->userId) || $client->userId !== $userId) {
                    $client->send(json_encode([
                        'type' => 'user_status',
                        'user_id' => $userId,
                        'status' => $status
                    ]));
                }
            }
        } catch (Exception $e) {
            error_log("User Status Broadcast Error: " . $e->getMessage());
        }
    }

    public function onClose(ConnectionInterface $conn) {
        try {
            $this->clients->detach($conn);
            
            if (isset($conn->userId)) {
                unset($this->users[$conn->userId]);
                $this->broadcastUserStatus($conn->userId, 'offline');
            }
            
            echo "Connection {$conn->resourceId} has disconnected\n";
        } catch (Exception $e) {
            error_log("Connection Close Error: " . $e->getMessage());
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("WebSocket Error: " . $e->getMessage());
        $conn->close();
    }
}
