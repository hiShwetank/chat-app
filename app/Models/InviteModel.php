<?php
namespace App\Models;

use App\Core\Database;

class InviteModel {
    private $db;
    private $table = 'invites';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create a new invite record
     * @param array $inviteData
     * @return bool
     */
    public function createInvite($inviteData) {
        $columns = implode(', ', array_keys($inviteData));
        $placeholders = ':' . implode(', :', array_keys($inviteData));
        
        $query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        return $this->db->prepare($query)->execute($inviteData);
    }

    /**
     * Get invite details by token
     * @param string $token
     * @return array|false
     */
    public function getInviteByToken($token) {
        $query = "SELECT * FROM {$this->table} WHERE invite_token = :token";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['token' => $token]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Update invite status
     * @param string $token
     * @param string $status
     * @return bool
     */
    public function updateInviteStatus($token, $status) {
        $query = "UPDATE {$this->table} SET status = :status WHERE invite_token = :token";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'status' => $status,
            'token' => $token
        ]);
    }

    /**
     * Clean up expired invites
     * @return int Number of deleted invites
     */
    public function cleanupExpiredInvites() {
        $query = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
