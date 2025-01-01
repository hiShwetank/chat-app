<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/autoload.php';

use App\Services\DatabaseService;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

try {
    // Run migrations
    DatabaseService::runMigrations();
    echo "Migrations completed successfully.\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    error_log("Migration Error: " . $e->getMessage());
    exit(1);
}
