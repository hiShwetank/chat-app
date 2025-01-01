<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Src\WebSocket\ChatServer;

// Correct autoload path
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new ChatServer()
            )
        ),
        8080
    );

    echo "WebSocket server running on port 8080\n";
    $server->run();
} catch (Exception $e) {
    echo "Server Error: " . $e->getMessage() . "\n";
    error_log("WebSocket Server Error: " . $e->getMessage());
}
