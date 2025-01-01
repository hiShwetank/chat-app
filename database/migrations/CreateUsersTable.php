<?php
namespace Database\Migrations;

use PDO;

class CreateUsersTable {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function up() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                profile_picture TEXT DEFAULT NULL,
                status TEXT DEFAULT 'offline',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down() {
        $this->db->exec("DROP TABLE IF EXISTS users");
    }
}
