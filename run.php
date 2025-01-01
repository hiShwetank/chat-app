<?php
// Chat Application Launcher

// Error Reporting and Logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');

// Dependency Check
function checkDependencies() {
    $requiredExtensions = [
        'sqlite3',
        'pdo',
        'pdo_sqlite',
        'json',
        'openssl',
        'mbstring'
    ];

    $missingExtensions = [];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }

    if (!empty($missingExtensions)) {
        die("Missing PHP Extensions: " . implode(', ', $missingExtensions));
    }
}

// Environment Configuration
function loadEnvironment() {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        die("Environment file (.env) not found!");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Database Initialization
function initializeDatabase() {
    $dbPath = $_ENV['DB_DATABASE'] ?? 'database/chat.sqlite';
    
    if (!file_exists($dbPath)) {
        echo "Initializing database...\n";
        require_once 'database/init_db.php';
    }
}

// Composer Dependency Check
function checkComposerDependencies() {
    if (!file_exists('vendor/autoload.php')) {
        echo "Installing Composer dependencies...\n";
        exec('composer install', $output, $returnVar);
        if ($returnVar !== 0) {
            die("Failed to install dependencies");
        }
    }
    require_once 'vendor/autoload.php';
}

// Cross-platform Process Management
function isProcessRunning($pidFile) {
    if (!file_exists($pidFile)) {
        return false;
    }

    $pid = trim(file_get_contents($pidFile));
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        exec("tasklist /FI \"PID eq $pid\"", $output);
        return count($output) > 1;
    } else {
        // Unix-like systems
        exec("ps -p $pid", $output);
        return count($output) > 1;
    }
}

function killProcess($pidFile) {
    if (!file_exists($pidFile)) {
        return;
    }

    $pid = trim(file_get_contents($pidFile));
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        exec("taskkill /F /PID $pid", $output, $returnVar);
    } else {
        // Unix-like systems
        exec("kill -9 $pid", $output, $returnVar);
    }

    unlink($pidFile);
}

// WebSocket Server Management
function startWebSocketServer() {
    $pidFile = 'websocket.pid';
    
    if (isProcessRunning($pidFile)) {
        echo "WebSocket server already running.\n";
        return;
    }

    echo "Starting WebSocket server...\n";
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows: Use start command to run in background
        $command = "start /B php src/WebSocket/server.php > websocket.log 2>&1";
        pclose(popen($command, "r"));
        
        // Get the last started process PID
        exec("wmic process where \"name='php.exe' and commandline like '%server.php%'\" get processid", $output);
        $pid = trim($output[1] ?? '');
        
        file_put_contents($pidFile, $pid);
    } else {
        // Unix-like systems
        $command = "php src/WebSocket/server.php > websocket.log 2>&1 & echo $! > {$pidFile}";
        exec($command);
    }
}

// PHP Built-in Server
function startPhpServer() {
    $pidFile = 'php_server.pid';
    $host = $_ENV['APP_HOST'] ?? 'localhost';
    $port = $_ENV['APP_PORT'] ?? 8000;

    if (isProcessRunning($pidFile)) {
        echo "PHP server already running.\n";
        return;
    }

    echo "Starting PHP built-in server at http://{$host}:{$port}\n";
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows: Use start command to run in background
        $command = "start /B php -S {$host}:{$port} -t public > php_server.log 2>&1";
        pclose(popen($command, "r"));
        
        // Get the last started process PID
        exec("wmic process where \"name='php.exe' and commandline like '%{$host}:{$port}%'\" get processid", $output);
        $pid = trim($output[1] ?? '');
        
        file_put_contents($pidFile, $pid);
    } else {
        // Unix-like systems
        $command = "php -S {$host}:{$port} -t public > php_server.log 2>&1 & echo $! > {$pidFile}";
        exec($command);
    }
}

// Stop Servers
function stopServers() {
    killProcess('websocket.pid');
    killProcess('php_server.pid');
    echo "All servers stopped.\n";
}

// Application Health Check
function healthCheck() {
    $checks = [
        'Database Connection' => function() {
            try {
                $db = new SQLite3($_ENV['DB_DATABASE']);
                return true;
            } catch (Exception $e) {
                return false;
            }
        },
        'WebSocket Server' => function() {
            $host = $_ENV['WEBSOCKET_HOST'] ?? 'localhost';
            $port = $_ENV['WEBSOCKET_PORT'] ?? 8080;
            $fp = @fsockopen($host, $port, $errno, $errstr, 5);
            return $fp !== false;
        }
    ];

    echo "Running Health Checks:\n";
    foreach ($checks as $name => $check) {
        $result = $check() ? '✅ Passed' : '❌ Failed';
        echo "{$name}: {$result}\n";
    }
}

// Main Execution
function main($action = 'start') {
    checkDependencies();
    loadEnvironment();
    checkComposerDependencies();

    switch ($action) {
        case 'start':
            initializeDatabase();
            startWebSocketServer();
            startPhpServer();
            healthCheck();
            break;
        
        case 'stop':
            stopServers();
            break;
        
        case 'restart':
            stopServers();
            main('start');
            break;
        
        case 'status':
            healthCheck();
            break;
    }
}

// CLI Argument Handling
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'start';
    main($action);
} else {
    // Web Entry Point
    main('start');
    header('Location: /login');
    exit;
}
