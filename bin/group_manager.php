<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Models\GroupModel;
use App\Services\DatabaseService;

class GroupManager {
    private $db;
    private $groupModel;

    public function __construct() {
        $this->db = DatabaseService::getConnection();
        $this->groupModel = new GroupModel($this->db);
    }

    public function createGroup($name, $description, $creatorId) {
        try {
            $groupId = $this->groupModel->createGroup($name, $description, $creatorId);
            echo "Group created successfully. Group ID: {$groupId}\n";
            return $groupId;
        } catch (Exception $e) {
            echo "Error creating group: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function addMemberToGroup($groupId, $userId, $role = 'member') {
        try {
            $this->groupModel->addMemberToGroup($groupId, $userId, $role);
            echo "User added to group successfully.\n";
        } catch (Exception $e) {
            echo "Error adding member to group: " . $e->getMessage() . "\n";
        }
    }

    public function listGroups($userId = null) {
        try {
            $groups = $userId 
                ? $this->groupModel->getUserGroups($userId)
                : $this->groupModel->getAllGroups();
            
            echo "Groups:\n";
            foreach ($groups as $group) {
                echo "ID: {$group['id']}, Name: {$group['name']}, Description: {$group['description']}\n";
            }
        } catch (Exception $e) {
            echo "Error listing groups: " . $e->getMessage() . "\n";
        }
    }

    public function showHelp() {
        echo "Group Management CLI\n";
        echo "Usage:\n";
        echo "  create <name> <description> <creator_id>   - Create a new group\n";
        echo "  add <group_id> <user_id> [role]           - Add user to group\n";
        echo "  list [user_id]                            - List groups\n";
        echo "  help                                      - Show this help message\n";
    }
}

// CLI Interface
$manager = new GroupManager();

if ($argc < 2) {
    $manager->showHelp();
    exit(1);
}

$command = $argv[1];

switch ($command) {
    case 'create':
        if ($argc < 5) {
            echo "Usage: php group_manager.php create <name> <description> <creator_id>\n";
            exit(1);
        }
        $manager->createGroup($argv[2], $argv[3], $argv[4]);
        break;

    case 'add':
        if ($argc < 4) {
            echo "Usage: php group_manager.php add <group_id> <user_id> [role]\n";
            exit(1);
        }
        $role = $argc > 4 ? $argv[4] : 'member';
        $manager->addMemberToGroup($argv[2], $argv[3], $role);
        break;

    case 'list':
        $userId = $argc > 2 ? $argv[2] : null;
        $manager->listGroups($userId);
        break;

    case 'help':
        $manager->showHelp();
        break;

    default:
        echo "Unknown command. Use 'help' for usage information.\n";
        exit(1);
}
