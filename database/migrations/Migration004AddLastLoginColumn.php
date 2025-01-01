<?php
namespace Database\Migrations;

use PDO;
use Exception;

class Migration004AddLastLoginColumn {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function up() {
        try {
            // Add last_login column to users table
            $alterTableQuery = "
                ALTER TABLE users 
                ADD COLUMN last_login DATETIME NULL
            ";
            $this->db->exec($alterTableQuery);
            echo "Added last_login column to users table.\n";
        } catch (Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw new Exception("Error adding last_login column: " . $e->getMessage());
        }
    }

    public function down() {
        try {
            // Remove last_login column from users table
            $alterTableQuery = "
                ALTER TABLE users 
                DROP COLUMN last_login
            ";
            $this->db->exec($alterTableQuery);
            echo "Removed last_login column from users table.\n";
        } catch (Exception $e) {
            error_log("Rollback Error: " . $e->getMessage());
            throw new Exception("Error removing last_login column: " . $e->getMessage());
        }
    }
}
