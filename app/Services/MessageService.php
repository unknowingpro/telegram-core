<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\MessageModel;
use App\Models\ChatModel;
use App\Models\ChatMemberModel;
use App\Models\UpdateModel;
use App\Models\UserModel;

/**
 * Message service — handles message delivery, processing, and broadcast
 */
class MessageService
{
    private MessageModel $messages;
    private ChatModel $chats;
    private UpdateModel $updates;
    private UserModel $users;

    public function __construct()
    {
        $this->messages = new MessageModel();
        $this->chats = new ChatModel();
        $this->updates = new UpdateModel();
        $this->users = new UserModel();
    }

    /**
     * Send a text message
     */
    public function sendText(int|string $chatId, int|string $senderId, string $text, array $options = []): array
    {
        // Verify sender is member of chat
        if (!$this->chats->isMember($chatId, $senderId)) {
            throw new \InvalidArgumentException('User is not a member of this chat');
        }

        // Create message
        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'text' => $text,
            'content_type' => 'text',
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'entities' => !empty($options['entities']) ? json_encode($options['entities']) : null,
            'is_protected' => $options['protect_content'] ?? false,
        ]);

        $message = $this->messages->find($messageId);

        // Broadcast to chat members
        $this->broadcastToChat($chatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Send a photo message
     */
    public function sendPhoto(int|string $chatId, int|string $senderId, string $fileId, array $options = []): array
    {
        if (!$this->chats->isMember($chatId, $senderId)) {
            throw new \InvalidArgumentException('User is not a member of this chat');
        }

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'photo',
            'content_data' => json_encode(['file_id' => $fileId]),
            'caption' => $options['caption'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
        ]);

        $message = $this->messages->find($messageId);
        $this->broadcastToChat($chatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Edit a text message
     */
    public function editText(int|string $chatId, int|string $messageId, string $text, array $options = []): array
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        $this->messages->editMessage($messageId, [
            'text' => $text,
            'entities' => !empty($options['entities']) ? json_encode($options['entities']) : null,
        ]);

        $updated = $this->messages->find($messageId);
        return $this->messages->toTelegram($updated);
    }

    /**
     * Delete a message
     */
    public function deleteMessage(int|string $chatId, int|string $messageId): bool
    {
        return $this->messages->deleteMessage($chatId, $messageId) > 0;
    }

    /**
     * Forward a message
     */
    public function forwardMessage(
        int|string $fromChatId,
        int|string $toChatId,
        int|string $messageId,
        int|string $senderId
    ): ?array {
        $newMessageId = $this->messages->forwardMessage($fromChatId, $toChatId, $messageId, $senderId);
        if (!$newMessageId) {
            return null;
        }

        $message = $this->messages->find($newMessageId);
        $this->broadcastToChat($toChatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Get chat history
     */
    public function getHistory(int|string $chatId, int $limit = 50, int $offset = 0, ?int $beforeId = null): array
    {
        $messages = $this->messages->getChatMessages($chatId, $limit, $offset, $beforeId);
        return array_map([$this->messages, 'toTelegram'], $messages);
    }

    /**
     * Broadcast an update to all chat members (except sender)
     */
    private function broadcastToChat(int|string $chatId, string $type, array $payload, ?int|string $excludeUserId = null): void
    {
        $members = (new ChatMemberModel())->getChatMembers($chatId);

        foreach ($members as $member) {
            if ($excludeUserId && (string) $member['user_id'] === (string) $excludeUserId) {
                continue;
            }

            $this->updates->pushUpdate($member['user_id'], $type, $payload);
        }
    }
}
