<?php
namespace App\Models;

use PDO;
use Exception;

class GroupModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Create a new group
    public function createGroup($name, $description, $creatorId) {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Insert group
            $query = "INSERT INTO groups (name, description, creator_id, created_at) 
                      VALUES (:name, :description, :creator_id, :created_at)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':creator_id' => $creatorId,
                ':created_at' => date('Y-m-d H:i:s')
            ]);

            // Get the new group ID
            $groupId = $this->db->lastInsertId();

            // Add creator as group admin
            $memberQuery = "INSERT INTO user_groups (user_id, group_id, role) 
                            VALUES (:user_id, :group_id, 'admin')";
            $memberStmt = $this->db->prepare($memberQuery);
            $memberStmt->execute([
                ':user_id' => $creatorId,
                ':group_id' => $groupId
            ]);

            // Commit transaction
            $this->db->commit();

            return [
                'id' => $groupId,
                'name' => $name,
                'description' => $description
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw new Exception("Group creation failed: " . $e->getMessage());
        }
    }

    // Add a member to a group
    public function addGroupMember($groupId, $userId, $role = 'member') {
        try {
            // Check if user is already in the group
            $checkQuery = "SELECT * FROM user_groups 
                           WHERE user_id = :user_id AND group_id = :group_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([
                ':user_id' => $userId,
                ':group_id' => $groupId
            ]);

            if ($checkStmt->fetch()) {
                throw new Exception("User is already a member of this group");
            }

            // Add user to group
            $query = "INSERT INTO user_groups (user_id, group_id, role) 
                      VALUES (:user_id, :group_id, :role)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':group_id' => $groupId,
                ':role' => $role
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to add group member: " . $e->getMessage());
        }
    }

    // Remove a member from a group
    public function removeGroupMember($groupId, $userId) {
        try {
            $query = "DELETE FROM user_groups 
                      WHERE user_id = :user_id AND group_id = :group_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':group_id' => $groupId
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to remove group member: " . $e->getMessage());
        }
    }

    // Get groups for a user
    public function getUserGroups($userId) {
        try {
            $query = "SELECT g.id, g.name, g.description, ug.role 
                      FROM groups g
                      JOIN user_groups ug ON g.id = ug.group_id
                      WHERE ug.user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Failed to fetch user groups: " . $e->getMessage());
        }
    }

    // Send a group message
    public function sendGroupMessage($groupId, $senderId, $message) {
        try {
            $query = "INSERT INTO group_messages (group_id, sender_id, message, sent_at) 
                      VALUES (:group_id, :sender_id, :message, :sent_at)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':group_id' => $groupId,
                ':sender_id' => $senderId,
                ':message' => $message,
                ':sent_at' => date('Y-m-d H:i:s')
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Failed to send group message: " . $e->getMessage());
        }
    }

    // Get group messages
    public function getGroupMessages($groupId, $limit = 50) {
        try {
            $query = "SELECT gm.*, u.username as sender_name 
                      FROM group_messages gm
                      JOIN users u ON gm.sender_id = u.id
                      WHERE gm.group_id = :group_id
                      ORDER BY gm.sent_at DESC
                      LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':group_id', $groupId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Failed to fetch group messages: " . $e->getMessage());
        }
    }
}
