<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Chat model — handles chat CRUD, types: private, group, supergroup, channel
 * Mirrors Telegram's Chat type
 */
class ChatModel extends BaseModel
{
    protected string $table = 'chats';
    protected string $primaryKey = 'id';

    /**
     * Create a private (1-on-1) chat between two users
     */
    public function createPrivateChat(int|string $user1Id, int|string $user2Id): string
    {
        // Check if private chat already exists
        $existing = $this->findPrivateChat($user1Id, $user2Id);
        if ($existing) {
            return (string) $existing['id'];
        }

        // Create chat with user IDs combined as title (Telegram-style)
        $chatId = $this->create([
            'type' => 'private',
            'title' => '',
            'member_count' => 2,
            'created_at' => now(),
        ]);

        // Add both members
        $memberModel = new ChatMemberModel();
        $memberModel->addMember($chatId, $user1Id, 'owner');
        $memberModel->addMember($chatId, $user2Id, 'member');

        return $chatId;
    }

    /**
     * Create a group/supergroup/channel
     */
    public function createGroup(array $data, int|string $creatorId): string
    {
        $data['type'] = $data['type'] ?? 'group';
        $data['member_count'] = 1;
        $data['created_at'] = now();

        $chatId = $this->create($data);

        // Add creator as owner
        $memberModel = new ChatMemberModel();
        $memberModel->addMember($chatId, $creatorId, 'owner');

        return $chatId;
    }

    /**
     * Find private chat between two users
     */
    public function findPrivateChat(int|string $user1Id, int|string $user2Id): ?array
    {
        return $this->db->rawFetch(
            "SELECT c.* FROM chats c
             JOIN chat_members cm1 ON cm1.chat_id = c.id AND cm1.user_id = ?
             JOIN chat_members cm2 ON cm2.chat_id = c.id AND cm2.user_id = ?
             WHERE c.type = 'private'",
            [$user1Id, $user2Id]
        );
    }

    /**
     * Get all chats for a user
     */
    public function getUserChats(int|string $userId, int $limit = 100): array
    {
        return $this->db->rawFetchAll(
            "SELECT c.* FROM chats c
             JOIN chat_members cm ON cm.chat_id = c.id AND cm.user_id = ? AND cm.status = 'active'
             ORDER BY c.id DESC
             LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Add a member to a chat
     */
    public function addMember(int|string $chatId, int|string $userId, string $role = 'member'): bool
    {
        $memberModel = new ChatMemberModel();
        return $memberModel->addMember($chatId, $userId, $role);
    }

    /**
     * Remove a member from a chat
     */
    public function removeMember(int|string $chatId, int|string $userId): bool
    {
        $memberModel = new ChatMemberModel();
        return $memberModel->removeMember($chatId, $userId);
    }

    /**
     * Check if user is a member of chat
     */
    public function isMember(int|string $chatId, int|string $userId): bool
    {
        $memberModel = new ChatMemberModel();
        $member = $memberModel->getMember($chatId, $userId);
        return $member && $member['status'] === 'active';
    }

    /**
     * Convert chat to Telegram-compatible array
     */
    public function toTelegram(array $chat): array
    {
        $result = [
            'id' => (int) $chat['id'],
            'type' => $chat['type'],
        ];

        if (!empty($chat['title'])) {
            $result['title'] = $chat['title'];
        }
        if (!empty($chat['username'])) {
            $result['username'] = $chat['username'];
        }
        if (!empty($chat['first_name'])) {
            $result['first_name'] = $chat['first_name'];
        }
        if (!empty($chat['last_name'])) {
            $result['last_name'] = $chat['last_name'];
        }
        if (!empty($chat['is_forum'])) {
            $result['is_forum'] = true;
        }

        return $result;
    }
}
