<?php
// Autoload configuration
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Custom autoloader for migrations
spl_autoload_register(function ($class) {
    $prefix = 'Database\\Migrations\\';
    $baseDir = dirname(__DIR__) . '/database/migrations/';

    // Check if the class uses the migration namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, $len);

    // Replace namespace separators with directory separators
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
