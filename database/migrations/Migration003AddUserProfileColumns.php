<?php
namespace Database\Migrations;

use PDO;
use Exception;

class Migration003AddUserProfileColumns {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function up() {
        // Add profile picture column
        $addProfilePictureQuery = "
            ALTER TABLE users 
            ADD COLUMN profile_picture TEXT NULL
        ";

        try {
            $this->db->exec($addProfilePictureQuery);
            echo "Added profile_picture column to users table.\n";
        } catch (Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw new Exception("Error adding profile_picture column: " . $e->getMessage());
        }

        // Create groups table
        $createGroupsTableQuery = "
            CREATE TABLE IF NOT EXISTS groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                created_by INTEGER,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )
        ";

        try {
            $this->db->exec($createGroupsTableQuery);
            echo "Created groups table.\n";
        } catch (Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw new Exception("Error creating groups table: " . $e->getMessage());
        }

        // Create user_groups table
        $createUserGroupsTableQuery = "
            CREATE TABLE IF NOT EXISTS user_groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                group_id INTEGER NOT NULL,
                role TEXT DEFAULT 'member',
                joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (group_id) REFERENCES groups(id),
                UNIQUE(user_id, group_id)
            )
        ";

        try {
            $this->db->exec($createUserGroupsTableQuery);
            echo "Created user_groups table.\n";
        } catch (Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw new Exception("Error creating user_groups table: " . $e->getMessage());
        }

        // Create friends table
        $createFriendsTableQuery = "
            CREATE TABLE IF NOT EXISTS friends (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                friend_id INTEGER NOT NULL,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (friend_id) REFERENCES users(id),
                UNIQUE(user_id, friend_id)
            )
        ";

        try {
            $this->db->exec($createFriendsTableQuery);
            echo "Created friends table.\n";
        } catch (Exception $e) {
            error_log("Migration Error: " . $e->getMessage());
            throw new Exception("Error creating friends table: " . $e->getMessage());
        }
    }

    public function down() {
        // Drop additional tables
        $dropTables = [
            "DROP TABLE IF EXISTS friends",
            "DROP TABLE IF EXISTS user_groups", 
            "DROP TABLE IF EXISTS groups"
        ];

        foreach ($dropTables as $query) {
            try {
                $this->db->exec($query);
            } catch (Exception $e) {
                error_log("Rollback Error: " . $e->getMessage());
            }
        }

        // Remove profile picture column
        try {
            $this->db->exec("
                BEGIN TRANSACTION;
                CREATE TABLE users_backup(
                    id, username, email, password, 
                    reset_token, reset_token_expiry, 
                    status, created_at, updated_at
                );
                INSERT INTO users_backup 
                SELECT id, username, email, password, 
                       reset_token, reset_token_expiry, 
                       status, created_at, updated_at 
                FROM users;
                DROP TABLE users;
                CREATE TABLE users(
                    id, username, email, password, 
                    reset_token, reset_token_expiry, 
                    status, created_at, updated_at
                );
                INSERT INTO users 
                SELECT id, username, email, password, 
                       reset_token, reset_token_expiry, 
                       status, created_at, updated_at 
                FROM users_backup;
                DROP TABLE users_backup;
                COMMIT;
            ");
        } catch (Exception $e) {
            error_log("Rollback Error: " . $e->getMessage());
            throw new Exception("Error removing profile_picture column: " . $e->getMessage());
        }
    }
}
