<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\WebSocketServer;

// Run WebSocket server
WebSocketServer::run();
