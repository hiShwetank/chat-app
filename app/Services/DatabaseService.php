<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use ReflectionClass;

class DatabaseService {
    private static $connection = null;

    public static function getConnection() {
        // If connection already exists, return it
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Load database configuration from .env
        $dbPath = $_ENV['DB_DATABASE'] ?? 'e:/ai/chat/chat-app/database/chat.sqlite';
        $dsn = "sqlite:{$dbPath}";

        try {
            // Create PDO connection
            self::$connection = new PDO($dsn);
            
            // Set error mode to exception
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign key support for SQLite
            self::$connection->exec('PRAGMA foreign_keys = ON');

            return self::$connection;
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Unable to connect to the database: " . $e->getMessage());
        }
    }

    // Run all migrations
    public static function runMigrations($migrationsPath = null) {
        if ($migrationsPath === null) {
            $migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        }

        $db = self::getConnection();

        // Ensure migrations table exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT UNIQUE NOT NULL,
                ran_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Get all migration files
        $migrationFiles = glob($migrationsPath . '/*.php');

        foreach ($migrationFiles as $migrationFile) {
            $migrationFileName = basename($migrationFile, '.php');
            $fullClassName = "Database\\Migrations\\" . $migrationFileName;

            // Skip if migration already ran
            $stmt = $db->prepare("SELECT * FROM migrations WHERE migration = :migration");
            $stmt->execute(['migration' => $migrationFileName]);
            if ($stmt->fetch()) {
                continue;
            }

            // Dynamically load and run migration
            require_once $migrationFile;

            try {
                $migrationClass = new $fullClassName($db);
                
                // Use reflection to check if up() method exists
                $reflectionClass = new ReflectionClass($migrationClass);
                if ($reflectionClass->hasMethod('up')) {
                    $migrationClass->up();

                    // Record migration
                    $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
                    $stmt->execute(['migration' => $migrationFileName]);
                }
            } catch (Exception $e) {
                error_log("Migration Error in {$migrationFileName}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    // Rollback last migration
    public static function rollbackLastMigration() {
        $db = self::getConnection();

        // Get last migration
        $stmt = $db->query("
            SELECT migration FROM migrations 
            ORDER BY ran_at DESC 
            LIMIT 1
        ");
        $lastMigration = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastMigration) {
            $migrationFileName = $lastMigration['migration'];
            $migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
            $migrationFile = $migrationsPath . '/' . $migrationFileName . '.php';

            require_once $migrationFile;

            $fullClassName = "Database\\Migrations\\" . $migrationFileName;
            $migrationClass = new $fullClassName($db);

            // Use reflection to check if down() method exists
            $reflectionClass = new ReflectionClass($migrationClass);
            if ($reflectionClass->hasMethod('down')) {
                $migrationClass->down();

                // Remove migration record
                $stmt = $db->prepare("DELETE FROM migrations WHERE migration = :migration");
                $stmt->execute(['migration' => $migrationFileName]);
            }
        }
    }

    // Prevent direct instantiation
    private function __construct() {}
    private function __clone() {}
}
