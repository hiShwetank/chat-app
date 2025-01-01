<?php
namespace Database\Migrations;

use PDO;

class CreateGroupsTables {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function up() {
        // Groups table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                creator_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // User Groups table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS user_groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                group_id INTEGER NOT NULL,
                role TEXT DEFAULT 'member',
                joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
                UNIQUE(user_id, group_id)
            )
        ");

        // Group Messages table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS group_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                group_id INTEGER NOT NULL,
                sender_id INTEGER NOT NULL,
                message TEXT NOT NULL,
                sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function down() {
        $this->db->exec("DROP TABLE IF EXISTS group_messages");
        $this->db->exec("DROP TABLE IF EXISTS user_groups");
        $this->db->exec("DROP TABLE IF EXISTS groups");
    }
}
