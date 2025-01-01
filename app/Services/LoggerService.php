<?php
namespace App\Services;

class LoggerService {
    private $logDirectory;
    private $logFile;

    public function __construct($logType = 'app') {
        // Ensure log directory exists
        $this->logDirectory = storage_path('logs');
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0755, true);
        }

        // Create log filename with date
        $this->logFile = $this->logDirectory . '/' . $logType . '_' . date('Y-m-d') . '.log';
    }

    /**
     * Log an error message
     * 
     * @param string $message Error message
     * @param array $context Additional context
     */
    public function error($message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message Info message
     * @param array $context Additional context
     */
    public function info($message, array $context = []) {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message Warning message
     * @param array $context Additional context
     */
    public function warning($message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log a debug message
     * 
     * @param string $message Debug message
     * @param array $context Additional context
     */
    public function debug($message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Core logging method
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     */
    private function log($level, $message, array $context = []) {
        // Prepare log entry
        $timestamp = date('Y-m-d H:i:s');
        $contextString = $context ? ' | ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$level}: {$message}{$contextString}\n";

        // Append to log file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);

        // Optional: Echo to console for immediate visibility
        if (env('APP_DEBUG', false)) {
            echo $logEntry;
        }
    }

    /**
     * Log an exception
     * 
     * @param \Throwable $exception Exception to log
     */
    public function exception(\Throwable $exception) {
        $message = sprintf(
            "Exception: %s in %s on line %d", 
            $exception->getMessage(), 
            $exception->getFile(), 
            $exception->getLine()
        );

        $this->error($message, [
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Clear old log files
     * 
     * @param int $days Number of days to keep logs
     */
    public function clearOldLogs($days = 30) {
        $files = glob($this->logDirectory . '/*_*.log');
        $now = time();

        foreach ($files as $file) {
            if ($now - filemtime($file) >= ($days * 24 * 60 * 60)) {
                unlink($file);
            }
        }
    }
}
