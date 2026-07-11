<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * ChatMember model — manages chat membership and roles
 */
class ChatMemberModel extends BaseModel
{
    protected string $table = 'chat_members';
    protected string $primaryKey = 'id';

    /**
     * Add a member to a chat
     */
    public function addMember(int|string $chatId, int|string $userId, string $role = 'member'): bool
    {
        // Check if already a member
        $existing = $this->getMember($chatId, $userId);

        if ($existing) {
            // Reactivate if previously left/kicked
            if ($existing['status'] !== 'active') {
                $this->update($existing['id'], [
                    'status' => 'active',
                    'role' => $role,
                    'joined_at' => now(),
                ]);
                return true;
            }
            return false; // Already active
        }

        $this->create([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'role' => $role,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Increment member count
        $chatModel = new ChatModel();
        $chat = $chatModel->find($chatId);
        if ($chat) {
            $chatModel->update($chatId, [
                'member_count' => ($chat['member_count'] ?? 0) + 1,
            ]);
        }

        return true;
    }

    /**
     * Remove a member from a chat (soft delete)
     */
    public function removeMember(int|string $chatId, int|string $userId): bool
    {
        $member = $this->getMember($chatId, $userId);
        if (!$member) {
            return false;
        }

        $this->update($member['id'], [
            'status' => 'left',
        ]);

        // Decrement member count
        $chatModel = new ChatModel();
        $chat = $chatModel->find($chatId);
        if ($chat && ($chat['member_count'] ?? 0) > 0) {
            $chatModel->update($chatId, [
                'member_count' => $chat['member_count'] - 1,
            ]);
        }

        return true;
    }

    /**
     * Get a specific member
     */
    public function getMember(int|string $chatId, int|string $userId): ?array
    {
        return $this->db->table($this->table)
            ->where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get all active members of a chat
     */
    public function getChatMembers(int|string $chatId): array
    {
        return $this->db->table($this->table)
            ->where('chat_id', $chatId)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get admin members of a chat
     */
    public function getChatAdmins(int|string $chatId): array
    {
        return $this->db->table($this->table)
            ->where('chat_id', $chatId)
            ->where('status', 'active')
            ->whereIn('role', ['admin', 'owner'])
            ->get();
    }

    /**
     * Update member role
     */
    public function setRole(int|string $chatId, int|string $userId, string $role): bool
    {
        $member = $this->getMember($chatId, $userId);
        if (!$member) {
            return false;
        }

        $this->update($member['id'], ['role' => $role]);
        return true;
    }

    /**
     * Ban a member (user or sender chat)
     *
     * Supports optional options: until_date, is_sender_chat
     * If member record doesn't exist (e.g., sender_chat), creates one
     */
    public function banMember(int|string $chatId, int|string $userId, array $options = []): bool
    {
        $member = $this->getMember($chatId, $userId);

        if (!$member) {
            $this->create([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'role' => 'member',
                'status' => 'kicked',
                'is_sender_chat' => !empty($options['is_sender_chat']),
                'banned_until' => $options['until_date'] ?? null,
                'joined_at' => now(),
            ]);
            return true;
        }

        $update = ['status' => 'kicked'];

        if (!empty($options['is_sender_chat'])) {
            $update['is_sender_chat'] = true;
        }
        if (!empty($options['until_date'])) {
            $update['banned_until'] = $options['until_date'];
        }

        $this->update($member['id'], $update);
        return true;
    }

    /**
     * Get user's chats
     */
    public function getUserChats(int|string $userId): array
    {
        return $this->db->table($this->table)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Update member by chat + user
     */
    public function updateMember(int|string $chatId, int|string $userId, array $data): bool
    {
        $member = $this->getMember($chatId, $userId);
        if (!$member) {
            return false;
        }
        return $this->update($member['id'], $data) > 0;
    }
}
