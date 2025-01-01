<?php
namespace Database\Migrations;

use PDO;
use Exception;

class Migration001CreateUsersTable {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function up() {
        // First, drop the table if it exists to ensure clean slate
        $dropTableQuery = "DROP TABLE IF EXISTS users";
        $this->db->exec($dropTableQuery);

        // Create users table with explicit column definitions
        $createUserTableQuery = "
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                reset_token TEXT NULL,
                reset_token_expiry DATETIME NULL,
                status TEXT DEFAULT 'offline',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        try {
            $this->db->exec($createUserTableQuery);
            echo "Users table created successfully.\n";

            // Verify table structure
            $this->verifyTableStructure();
        } catch (Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw new Exception("Error creating users table: " . $e->getMessage());
        }

        // Create index for faster email lookups
        $createEmailIndexQuery = "
            CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email 
            ON users (email)
        ";

        try {
            $this->db->exec($createEmailIndexQuery);
            echo "Email index created successfully.\n";
        } catch (Exception $e) {
            error_log("Index Creation Error: " . $e->getMessage());
            throw new Exception("Error creating email index: " . $e->getMessage());
        }
    }

    public function down() {
        // Drop users table
        $dropUserTableQuery = "DROP TABLE IF EXISTS users";

        try {
            $this->db->exec($dropUserTableQuery);
            echo "Users table dropped successfully.\n";
        } catch (Exception $e) {
            error_log("Rollback Error: " . $e->getMessage());
            throw new Exception("Error dropping users table: " . $e->getMessage());
        }
    }

    private function verifyTableStructure() {
        try {
            // Attempt to get table info
            $stmt = $this->db->query("PRAGMA table_info(users)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Print out column information for debugging
            echo "Table Columns:\n";
            foreach ($columns as $column) {
                echo "Name: {$column['name']}, Type: {$column['type']}, Nullable: " . 
                     ($column['notnull'] ? 'No' : 'Yes') . "\n";
            }
        } catch (Exception $e) {
            error_log("Table Structure Verification Error: " . $e->getMessage());
            throw new Exception("Unable to verify table structure: " . $e->getMessage());
        }
    }
}
