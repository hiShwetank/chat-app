<?php
require_once __DIR__ . '/../vendor/autoload.php';

use SQLite3;

class DatabaseInitializer {
    private $db;

    public function __construct($dbPath) {
        $this->db = new SQLite3($dbPath);
    }

    public function createTables() {
        // Users Table
        $this->db->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            profile_picture TEXT,
            status TEXT DEFAULT "offline",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )');

        // Friends Table
        $this->db->exec('CREATE TABLE IF NOT EXISTS friends (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            friend_id INTEGER NOT NULL,
            status TEXT DEFAULT "pending",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id),
            FOREIGN KEY(friend_id) REFERENCES users(id)
        )');

        // Messages Table
        $this->db->exec('CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER,
            content TEXT NOT NULL,
            type TEXT DEFAULT "text",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_read BOOLEAN DEFAULT 0,
            FOREIGN KEY(sender_id) REFERENCES users(id),
            FOREIGN KEY(receiver_id) REFERENCES users(id)
        )');

        // Groups Table
        $this->db->exec('CREATE TABLE IF NOT EXISTS groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            creator_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(creator_id) REFERENCES users(id)
        )');

        // Group Members Table
        $this->db->exec('CREATE TABLE IF NOT EXISTS group_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            role TEXT DEFAULT "member",
            joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(group_id) REFERENCES groups(id),
            FOREIGN KEY(user_id) REFERENCES users(id)
        )');

        echo "Database tables created successfully.\n";
    }

    public function close() {
        $this->db->close();
    }
}

// Usage
$dbPath = __DIR__ . '/chat.sqlite';
$initializer = new DatabaseInitializer($dbPath);
$initializer->createTables();
$initializer->close();
