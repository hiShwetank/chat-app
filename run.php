<?php
require_once 'vendor/autoload.php';

use App\Services\DatabaseService;
use App\Services\WebSocketServer;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutor;

class ApplicationManager {
    private $rootPath;
    private $processes = [];

    public function __construct() {
        $this->rootPath = dirname(__FILE__);
    }

    public function runMigrations() {
        try {
            DatabaseService::runMigrations();
            echo "Migrations completed successfully.\n";
        } catch (Exception $e) {
            echo "Migration Error: " . $e->getMessage() . "\n";
        }
    }

    public function startWebServer($port = 8000) {
        $command = [
            PHP_BINARY, 
            '-S', 
            "localhost:{$port}", 
            '-t', 
            "{$this->rootPath}/public"
        ];

        $process = new Process($command);
        $process->start();
        $this->processes['web_server'] = $process;

        echo "PHP built-in web server started on http://localhost:{$port}\n";
    }

    public function startWebSocketServer($port = 8080) {
        $command = [
            PHP_BINARY, 
            "{$this->rootPath}/bin/websocket_server.php"
        ];

        $process = new Process($command);
        $process->start();
        $this->processes['websocket_server'] = $process;

        echo "WebSocket server started on ws://localhost:{$port}\n";
    }

    public function startMailServer($port = 1025) {
        // Using Python's built-in SMTP debugging server
        $command = [
            'python', 
            '-m', 
            'smtpd', 
            '-n', 
            "-c", 
            "DebuggingServer localhost:{$port}"
        ];

        $process = new Process($command);
        $process->start();
        $this->processes['mail_server'] = $process;

        echo "SMTP Debug Server started on localhost:{$port}\n";
    }

    public function startDevEnvironment() {
        $this->runMigrations();
        $this->startWebServer();
        $this->startWebSocketServer();
        $this->startMailServer();
    }

    public function stopAllProcesses() {
        foreach ($this->processes as $name => $process) {
            if ($process->isRunning()) {
                $process->stop();
                echo "Stopped {$name}\n";
            }
        }
    }

    public function runCommand($command) {
        switch ($command) {
            case 'migrate':
                $this->runMigrations();
                break;
            case 'serve':
                $this->startWebServer();
                break;
            case 'websocket':
                $this->startWebSocketServer();
                break;
            case 'mail':
                $this->startMailServer();
                break;
            case 'dev':
                $this->startDevEnvironment();
                break;
            case 'stop':
                $this->stopAllProcesses();
                break;
            default:
                $this->printUsage();
        }
    }

    public function printUsage() {
        echo "Usage: php run.php [command]\n";
        echo "Commands:\n";
        echo "  migrate   - Run database migrations\n";
        echo "  serve     - Start PHP built-in web server\n";
        echo "  websocket - Start WebSocket server\n";
        echo "  mail      - Start SMTP debug server\n";
        echo "  dev       - Start full development environment\n";
        echo "  stop      - Stop all running processes\n";
    }
}

// Main execution
$manager = new ApplicationManager();

// Check if script is run from CLI
if (php_sapi_name() === 'cli') {
    // Get command from argument
    $command = $argv[1] ?? 'dev';
    
    try {
        $manager->runCommand($command);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $manager->printUsage();
    }
} else {
    // If accessed via web, show usage
    $manager->printUsage();
}
