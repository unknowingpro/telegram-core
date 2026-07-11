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
        int|string|null $fromUserId = null
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
     * Upsert a reaction for a message
     */
    public function upsertReaction(int|string $messageId, ?array $reaction, bool $isBig = false): bool
    {
        if ($reaction === null) {
            return $this->db->table('message_reactions')
                ->where('message_id', $messageId)
                ->delete() > 0;
        }

        $type = $reaction['type'] ?? 'emoji';
        $emoji = $reaction['emoji'] ?? null;
        $customEmojiId = $reaction['custom_emoji_id'] ?? null;

        $existing = $this->db->table('message_reactions')
            ->where('message_id', $messageId)
            ->first();

        if ($existing) {
            return $this->db->table('message_reactions')
                ->where('message_id', $messageId)
                ->update([
                    'reaction_type' => $type,
                    'emoji' => $emoji,
                    'custom_emoji_id' => $customEmojiId,
                ]) > 0;
        }

        return $this->db->table('message_reactions')->insert([
            'message_id' => $messageId,
            'user_id' => 0,
            'reaction_type' => $type,
            'emoji' => $emoji,
            'custom_emoji_id' => $customEmojiId,
        ]) > 0;
    }

    /**
     * Delete a reaction by user
     */
    public function deleteReaction(int|string $messageId, int|string $userId): bool
    {
        return $this->db->table('message_reactions')
            ->where('message_id', $messageId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Delete all reactions for a message
     */
    public function deleteAllReactions(int|string $messageId): bool
    {
        return $this->db->table('message_reactions')
            ->where('message_id', $messageId)
            ->delete() > 0;
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

        // Optional top-level fields
        if (!empty($message['message_thread_id'])) {
            $result['is_topic_message'] = true;
            $result['message_thread_id'] = (int) $message['message_thread_id'];
        }
        if (!empty($message['business_connection_id'])) {
            $result['business_connection_id'] = $message['business_connection_id'];
        }

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
        if (!empty($message['caption_entities'])) {
            $result['caption_entities'] = json_decode($message['caption_entities'], true);
        }

        // Media attributes
        if (!empty($message['has_media_spoiler'])) {
            $result['has_media_spoiler'] = (bool) $message['has_media_spoiler'];
        }
        if (!empty($message['media_group_id'])) {
            $result['media_group_id'] = $message['media_group_id'];
        }
        if (!empty($message['show_caption_above_media'])) {
            $result['show_caption_above_media'] = (bool) $message['show_caption_above_media'];
        }

        // Reply info
        if (!empty($message['reply_to_message_id'])) {
            $replyTo = $this->find($message['reply_to_message_id']);
            if ($replyTo) {
                $result['reply_to_message'] = $this->toTelegram($replyTo);
            }
        }
        if (!empty($message['reply_parameters'])) {
            $result['reply_parameters'] = json_decode($message['reply_parameters'], true);
        }

        // Forward info
        if (!empty($message['forward_from_id'])) {
            $forwardUser = (new UserModel())->find($message['forward_from_id']);
            if ($forwardUser) {
                $result['forward_from'] = (new UserModel())->toTelegram($forwardUser);
            }
        }
        if (!empty($message['forward_from_chat_id'])) {
            $forwardChat = (new ChatModel())->find($message['forward_from_chat_id']);
            if ($forwardChat) {
                $result['forward_from_chat'] = (new ChatModel())->toTelegram($forwardChat);
            }
        }
        if (!empty($message['forward_from_message_id'])) {
            $result['forward_from_message_id'] = (int) $message['forward_from_message_id'];
        }
        if (!empty($message['forward_date'])) {
            $result['forward_date'] = is_numeric($message['forward_date'])
                ? (int) $message['forward_date']
                : strtotime($message['forward_date']);
        }

        // Author signature
        if (!empty($message['author_signature'])) {
            $result['author_signature'] = $message['author_signature'];
        }

        // Edit date
        if (!empty($message['edit_date'])) {
            $result['edit_date'] = strtotime($message['edit_date']);
        }

        // Content type (photo, video, audio, document, etc.)
        if (!empty($message['content_type']) && $message['content_type'] !== 'text') {
            $contentData = json_decode($message['content_data'] ?? '{}', true);
            $result[$message['content_type']] = $contentData;
        }

        return $result;
    }
}
