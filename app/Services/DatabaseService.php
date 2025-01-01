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
    public static function runMigrations() {
        try {
            $db = self::getConnection();

            // List of migration classes
            $migrations = [
                '\Database\Migrations\CreateUsersTable',
                '\Database\Migrations\CreateFriendshipTables',
                '\Database\Migrations\CreateGroupsTables'
            ];

            foreach ($migrations as $migrationClass) {
                // Dynamically instantiate and run migration
                $migration = new $migrationClass($db);
                
                // Check if migration table exists
                $tableName = strtolower(substr(strrchr($migrationClass, '\\'), 1));
                $checkTableQuery = "SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'";
                $checkStmt = $db->query($checkTableQuery);
                $tableExists = $checkStmt->fetch();

                if (!$tableExists) {
                    // Create migrations table if not exists
                    $db->exec("
                        CREATE TABLE IF NOT EXISTS migrations (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            migration TEXT NOT NULL,
                            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        )
                    ");
                }

                // Check if migration has been applied
                $checkMigrationQuery = "SELECT * FROM migrations WHERE migration = :migration";
                $checkMigrationStmt = $db->prepare($checkMigrationQuery);
                $checkMigrationStmt->execute([':migration' => $migrationClass]);
                
                if (!$checkMigrationStmt->fetch()) {
                    // Run migration
                    $migration->up();

                    // Record migration
                    $insertMigrationQuery = "
                        INSERT INTO migrations (migration) 
                        VALUES (:migration)
                    ";
                    $insertStmt = $db->prepare($insertMigrationQuery);
                    $insertStmt->execute([':migration' => $migrationClass]);
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw $e;
        }
    }

    // Rollback last migration
    public static function rollbackLastMigration() {
        $db = self::getConnection();

        // Get last migration
        $stmt = $db->query("
            SELECT migration FROM migrations 
            ORDER BY applied_at DESC 
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
                $stmt->execute([':migration' => $migrationFileName]);
            }
        }
    }

    // Prevent direct instantiation
    private function __construct() {}
    private function __clone() {}
}
