<?php
namespace Database\Migrations;

use PDO;

class CreateFriendshipTables {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function up() {
        // Friends table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS friends (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                friend_id INTEGER NOT NULL,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(user_id, friend_id)
            )
        ");

        // Friend requests table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS friend_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                sender_id INTEGER NOT NULL,
                receiver_id INTEGER NOT NULL,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(sender_id, receiver_id)
            )
        ");
    }

    public function down() {
        $this->db->exec("DROP TABLE IF EXISTS friend_requests");
        $this->db->exec("DROP TABLE IF EXISTS friends");
    }
}
