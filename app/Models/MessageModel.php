<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Message model — handles message CRUD and queries
 * Mirrors Telegram's Message type with 50+ fields
 */
class MessageModel extends BaseModel
{
    protected string $table = 'messages';
    protected string $primaryKey = 'id';

    /**
     * Send a new message
     */
    public function sendMessage(array $data): string
    {
        $data['id'] = $this->generateId();
        $data['created_at'] = now();

        return $this->create($data);
    }

    /**
     * Get messages for a chat
     */
    public function getChatMessages(
        int|string $chatId,
        int $limit = 50,
        int $offset = 0,
        ?int $beforeId = null
    ): array {
        $query = $this->db->table($this->table)
            ->where('chat_id', $chatId)
            ->where('deleted_at', null);

        if ($beforeId) {
            $query = $query->where('id', '<', $beforeId);
        }

        return $query->orderBy('id', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get a specific message
     */
    public function getMessage(int|string $chatId, int|string $messageId): ?array
    {
        return $this->db->table($this->table)
            ->where('chat_id', $chatId)
            ->where('id', $messageId)
            ->first();
    }

    /**
     * Edit a message
     */
    public function editMessage(int|string $messageId, array $data): int
    {
        $data['edit_date'] = now();
        return $this->update($messageId, $data);
    }

    /**
     * Delete a message (soft delete)
     */
    public function deleteMessage(int|string $chatId, int|string $messageId): int
    {
        return $this->db->table($this->table)
            ->where('chat_id', $chatId)
            ->where('id', $messageId)
            ->update(['deleted_at' => now()]);
    }

    /**
     * Forward a message to another chat
     */
    public function forwardMessage(
        int|string $fromChatId,
        int|string $toChatId,
        int|string $messageId,
        ?int|string $fromUserId = null
    ): ?string {
        $original = $this->getMessage($fromChatId, $messageId);
        if (!$original) {
            return null;
        }

        $forwardData = [
            'chat_id' => $toChatId,
            'sender_id' => $fromUserId ?? $original['sender_id'],
            'text' => $original['text'],
            'caption' => $original['caption'],
            'content_type' => $original['content_type'],
            'content_data' => $original['content_data'],
            'entities' => $original['entities'],
            'forward_from_id' => $original['sender_id'],
        ];

        return $this->sendMessage($forwardData);
    }

    /**
     * Get message count for a chat
     */
    public function countChatMessages(int|string $chatId): int
    {
        return $this->count(['chat_id' => $chatId]);
    }

    /**
     * Search messages in a chat
     */
    public function searchMessages(int|string $chatId, string $query, int $limit = 50): array
    {
        return $this->db->rawFetchAll(
            "SELECT * FROM {$this->table}
             WHERE chat_id = ? AND deleted_at IS NULL
             AND MATCH(text) AGAINST(? IN BOOLEAN MODE)
             ORDER BY id DESC
             LIMIT ?",
            [$chatId, $query, $limit]
        );
    }

    /**
     * Convert message to Telegram-compatible array
     */
    public function toTelegram(array $message): array
    {
        $result = [
            'message_id' => (int) $message['id'],
            'date' => strtotime($message['created_at']),
        ];

        // Sender info
        if (!empty($message['sender_id'])) {
            $userModel = new UserModel();
            $user = $userModel->find($message['sender_id']);
            if ($user) {
                $result['from'] = $userModel->toTelegram($user);
            }
        }

        // Chat info
        if (!empty($message['chat_id'])) {
            $chatModel = new ChatModel();
            $chat = $chatModel->find($message['chat_id']);
            if ($chat) {
                $result['chat'] = $chatModel->toTelegram($chat);
            }
        }

        // Text content
        if (!empty($message['text'])) {
            $result['text'] = $message['text'];
        }
        if (!empty($message['entities'])) {
            $result['entities'] = json_decode($message['entities'], true);
        }
        if (!empty($message['caption'])) {
            $result['caption'] = $message['caption'];
        }

        // Reply info
        if (!empty($message['reply_to_message_id'])) {
            $replyTo = $this->find($message['reply_to_message_id']);
            if ($replyTo) {
                $result['reply_to_message'] = $this->toTelegram($replyTo);
            }
        }

        // Forward info
        if (!empty($message['forward_from_id'])) {
            $forwardUser = (new UserModel())->find($message['forward_from_id']);
            if ($forwardUser) {
                $result['forward_from'] = (new UserModel())->toTelegram($forwardUser);
            }
        }

        // Edit date
        if (!empty($message['edit_date'])) {
            $result['edit_date'] = strtotime($message['edit_date']);
        }

        // Content type
        if (!empty($message['content_type']) && $message['content_type'] !== 'text') {
            $contentData = json_decode($message['content_data'] ?? '{}', true);
            $result[$message['content_type']] = $contentData;
        }

        return $result;
    }
}
