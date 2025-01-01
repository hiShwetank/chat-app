<?php
namespace App\Services;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $users;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        $this->users = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        switch ($data['type']) {
            case 'authenticate':
                $this->authenticateUser($from, $data);
                break;
            
            case 'friend_request':
                $this->handleFriendRequest($from, $data);
                break;
            
            case 'message':
                $this->broadcastMessage($from, $data);
                break;
            
            case 'group_message':
                $this->broadcastGroupMessage($from, $data);
                break;
        }
    }

    protected function authenticateUser(ConnectionInterface $conn, $data) {
        $userId = $data['user_id'];
        $this->users[$conn->resourceId] = $userId;
        $conn->send(json_encode([
            'type' => 'authentication',
            'status' => 'success',
            'message' => 'Connected successfully'
        ]));
    }

    protected function handleFriendRequest(ConnectionInterface $from, $data) {
        $senderId = $this->users[$from->resourceId];
        $receiverId = $data['receiver_id'];

        // Find connection for receiver
        $receiverConn = $this->findConnectionByUserId($receiverId);
        
        if ($receiverConn) {
            $receiverConn->send(json_encode([
                'type' => 'friend_request',
                'sender_id' => $senderId,
                'message' => 'New friend request'
            ]));
        }
    }

    protected function broadcastMessage(ConnectionInterface $from, $data) {
        $senderId = $this->users[$from->resourceId];
        $receiverId = $data['receiver_id'];

        $receiverConn = $this->findConnectionByUserId($receiverId);
        
        if ($receiverConn) {
            $receiverConn->send(json_encode([
                'type' => 'message',
                'sender_id' => $senderId,
                'message' => $data['message']
            ]));
        }
    }

    protected function broadcastGroupMessage(ConnectionInterface $from, $data) {
        $senderId = $this->users[$from->resourceId];
        $groupId = $data['group_id'];

        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send(json_encode([
                    'type' => 'group_message',
                    'group_id' => $groupId,
                    'sender_id' => $senderId,
                    'message' => $data['message']
                ]));
            }
        }
    }

    protected function findConnectionByUserId($userId) {
        foreach ($this->clients as $client) {
            if (isset($this->users[$client->resourceId]) && 
                $this->users[$client->resourceId] == $userId) {
                return $client;
            }
        }
        return null;
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->users[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public static function run() {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new self()
                )
            ),
            8080
        );

        echo "WebSocket server running on port 8080\n";
        $server->run();
    }
}
