<?php
// Comprehensive System Diagnostic Script

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check PHP Configuration
echo "=== PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server API: " . php_sapi_name() . "\n";

// Check Extensions
echo "\n=== Loaded Extensions ===\n";
print_r(get_loaded_extensions());

// Database Connection Test
echo "\n=== Database Connection Test ===\n";
try {
    $configPath = __DIR__ . '/config/database.php';
    if (file_exists($configPath)) {
        $config = require $configPath;
        
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        echo "Database Connection: Successful\n";
        
        // Test basic query
        $stmt = $pdo->query("SELECT 1");
        echo "Basic Query Test: " . ($stmt ? "Passed" : "Failed") . "\n";
    } else {
        echo "Database Config Not Found\n";
    }
} catch (PDOException $e) {
    echo "Database Connection Error: " . $e->getMessage() . "\n";
}

// Check File Permissions
echo "\n=== File Permissions ===\n";
$criticalDirs = [
    'app',
    'public',
    'config',
    'storage',
    'logs'
];

foreach ($criticalDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        echo "$dir: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
    }
}

// Check Routing
echo "\n=== Routing Test ===\n";
$routesPath = __DIR__ . '/config/routes.php';
if (file_exists($routesPath)) {
    $routes = require $routesPath;
    echo "Routes Loaded: " . count($routes) . "\n";
    print_r(array_keys($routes));
} else {
    echo "Routes Configuration Not Found\n";
}

// Environment Check
echo "\n=== Environment Variables ===\n";
print_r($_ENV);

// Server Variables
echo "\n=== Server Variables ===\n";
print_r($_SERVER);

// Log the diagnostic information
file_put_contents(__DIR__ . '/debug_log.txt', ob_get_contents());
