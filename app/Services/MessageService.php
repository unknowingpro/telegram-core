<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\MessageModel;
use App\Models\ChatModel;
use App\Models\ChatMemberModel;
use App\Models\UpdateModel;
use App\Models\UserModel;
use App\Models\MediaModel;

/**
 * Message service — handles message delivery, processing, and broadcast
 * Supports all Telegram message types
 */
class MessageService
{
    private MessageModel $messages;
    private ChatModel $chats;
    private UpdateModel $updates;
    private UserModel $users;
    private MediaModel $media;

    public function __construct()
    {
        $this->messages = new MessageModel();
        $this->chats = new ChatModel();
        $this->updates = new UpdateModel();
        $this->users = new UserModel();
        $this->media = new MediaModel();
    }

    /**
     * Send a text message
     */
    public function sendText(int|string $chatId, int|string $senderId, string $text, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'text' => $text,
            'content_type' => 'text',
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'entities' => isset($options['entities']) ? (is_string($options['entities']) ? $options['entities'] : json_encode($options['entities'])) : null,
            'parse_mode' => $options['parse_mode'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        $this->broadcastToChat($chatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Send a photo message
     */
    public function sendPhoto(int|string $chatId, int|string $senderId, string $photo, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = ['file_id' => $photo];
        if (!empty($options['has_spoiler'])) {
            $contentData['has_spoiler'] = true;
        }

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'photo',
            'content_data' => json_encode($contentData),
            'caption' => $options['caption'] ?? null,
            'caption_entities' => isset($options['caption_entities']) ? (is_string($options['caption_entities']) ? $options['caption_entities'] : json_encode($options['caption_entities'])) : null,
            'parse_mode' => $options['parse_mode'] ?? null,
            'show_caption_above_media' => $options['show_caption_above_media'] ?? false,
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        $this->broadcastToChat($chatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Send a media message (audio, video, document, animation, voice, video_note)
     */
    public function sendMedia(int|string $chatId, int|string $senderId, string $media, string $type, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = array_merge(['file_id' => $media], $options['media_data'] ?? []);
        if (!empty($options['has_spoiler'])) {
            $contentData['has_spoiler'] = true;
        }

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => $type,
            'content_data' => json_encode($contentData),
            'caption' => $options['caption'] ?? null,
            'caption_entities' => isset($options['caption_entities']) ? (is_string($options['caption_entities']) ? $options['caption_entities'] : json_encode($options['caption_entities'])) : null,
            'parse_mode' => $options['parse_mode'] ?? null,
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        $this->broadcastToChat($chatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Send location
     */
    public function sendLocation(int|string $chatId, int|string $senderId, float $latitude, float $longitude, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'horizontal_accuracy' => $options['horizontal_accuracy'] ?? null,
            'live_period' => $options['live_period'] ?? null,
            'heading' => $options['heading'] ?? null,
            'proximity_alert_radius' => $options['proximity_alert_radius'] ?? null,
        ];

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'location',
            'content_data' => json_encode(array_filter($contentData, fn($v) => $v !== null)),
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        $this->broadcastToChat($chatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Send venue
     */
    public function sendVenue(int|string $chatId, int|string $senderId, float $latitude, float $longitude, string $title, string $address, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = array_filter([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
            'foursquare_id' => $options['foursquare_id'] ?? null,
            'foursquare_type' => $options['foursquare_type'] ?? null,
            'google_place_id' => $options['google_place_id'] ?? null,
            'google_place_type' => $options['google_place_type'] ?? null,
        ], fn($v) => $v !== null);

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'venue',
            'content_data' => json_encode($contentData),
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        return $this->messages->toTelegram($message);
    }

    /**
     * Send contact
     */
    public function sendContact(int|string $chatId, int|string $senderId, string $phoneNumber, string $firstName, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = array_filter([
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
            'last_name' => $options['last_name'] ?? null,
            'vcard' => $options['vcard'] ?? null,
            'user_id' => $options['user_id'] ?? null,
        ], fn($v) => $v !== null);

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'contact',
            'content_data' => json_encode($contentData),
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        return $this->messages->toTelegram($message);
    }

    /**
     * Send dice
     */
    public function sendDice(int|string $chatId, int|string $senderId, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = [
            'emoji' => $options['emoji'] ?? '🎲',
            'value' => random_int(1, 6),
        ];

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'dice',
            'content_data' => json_encode($contentData),
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
        ]);

        $message = $this->messages->find($messageId);
        return $this->messages->toTelegram($message);
    }

    /**
     * Send poll
     */
    public function sendPoll(int|string $chatId, int|string $senderId, string $question, array $optionsList, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $contentData = [
            'question' => $question,
            'options' => $optionsList,
            'is_anonymous' => $options['is_anonymous'] ?? true,
            'type' => $options['type'] ?? 'regular',
            'allows_multiple_answers' => $options['allows_multiple_answers'] ?? false,
            'correct_option_id' => $options['correct_option_id'] ?? null,
            'correct_option_ids' => $options['correct_option_ids'] ?? null,
            'explanation' => $options['explanation'] ?? null,
            'explanation_parse_mode' => $options['explanation_parse_mode'] ?? null,
            'explanation_media' => $options['explanation_media'] ?? null,
            'open_period' => $options['open_period'] ?? null,
            'close_date' => $options['close_date'] ?? null,
            'is_closed' => $options['is_closed'] ?? false,
            'question_parse_mode' => $options['question_parse_mode'] ?? null,
            'question_entities' => $options['question_entities'] ?? null,
            'allows_revoting' => $options['allows_revoting'] ?? false,
            'shuffle_options' => $options['shuffle_options'] ?? null,
            'allow_adding_options' => $options['allow_adding_options'] ?? false,
            'hide_results_until_closes' => $options['hide_results_until_closes'] ?? false,
            'members_only' => $options['members_only'] ?? false,
            'country_codes' => $options['country_codes'] ?? null,
            'description' => $options['description'] ?? null,
            'description_parse_mode' => $options['description_parse_mode'] ?? null,
            'description_entities' => $options['description_entities'] ?? null,
            'media' => $options['media'] ?? null,
        ];

        $messageId = $this->messages->sendMessage([
            'chat_id' => $chatId,
            'sender_id' => $senderId,
            'content_type' => 'poll',
            'content_data' => json_encode($contentData),
            'message_thread_id' => $options['message_thread_id'] ?? null,
            'reply_to_message_id' => $options['reply_to_message_id'] ?? null,
            'reply_parameters' => $options['reply_parameters'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
            'is_protected' => $options['protect_content'] ?? false,
            'disable_notification' => $options['disable_notification'] ?? false,
            'allow_paid_broadcast' => $options['allow_paid_broadcast'] ?? false,
            'message_effect_id' => $options['message_effect_id'] ?? null,
        ]);

        $message = $this->messages->find($messageId);
        return $this->messages->toTelegram($message);
    }

    /**
     * Send chat action
     */
    public function sendChatAction(int|string $chatId, string $action): bool
    {
        // Chat actions are ephemeral — just validate the action type
        $validActions = [
            'typing', 'upload_photo', 'record_video', 'upload_video',
            'record_voice', 'upload_voice', 'upload_document', 'choose_sticker',
            'find_location', 'record_video_note', 'upload_video_note',
        ];

        if (!in_array($action, $validActions)) {
            throw new \InvalidArgumentException("Invalid chat action: {$action}");
        }

        return true;
    }

    /**
     * Send media group (multiple photos/videos in one message)
     */
    public function sendMediaGroup(int|string $chatId, int|string $senderId, array $media, array $options = []): array
    {
        $this->verifyMember($chatId, $senderId);

        $results = [];
        foreach ($media as $item) {
            $type = $item['type'] ?? 'photo';
            $fileId = $item['media'] ?? '';

            $result = $this->sendMedia($chatId, $senderId, $fileId, $type, [
                'caption' => $item['caption'] ?? null,
                'parse_mode' => $item['parse_mode'] ?? null,
                'message_thread_id' => $options['message_thread_id'] ?? null,
                'disable_notification' => $options['disable_notification'] ?? false,
                'protect_content' => $options['protect_content'] ?? false,
            ]);
            $results[] = $result;
        }

        return $results;
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
            'entities' => isset($options['entities']) ? (is_string($options['entities']) ? $options['entities'] : json_encode($options['entities'])) : null,
            'parse_mode' => $options['parse_mode'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
        ]);

        $updated = $this->messages->find($messageId);
        return $this->messages->toTelegram($updated);
    }

    /**
     * Edit message caption
     */
    public function editCaption(int|string $chatId, int|string $messageId, ?string $caption, array $options = []): array
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        $this->messages->editMessage($messageId, [
            'caption' => $caption,
            'caption_entities' => isset($options['caption_entities']) ? (is_string($options['caption_entities']) ? $options['caption_entities'] : json_encode($options['caption_entities'])) : null,
            'parse_mode' => $options['parse_mode'] ?? null,
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
        ]);

        $updated = $this->messages->find($messageId);
        return $this->messages->toTelegram($updated);
    }

    /**
     * Edit message media
     */
    public function editMedia(int|string $chatId, int|string $messageId, array $media, array $options = []): array
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        $type = $media['type'] ?? 'photo';
        $fileId = $media['media'] ?? '';
        $contentData = ['file_id' => $fileId];

        $this->messages->editMessage($messageId, [
            'content_type' => $type,
            'content_data' => json_encode($contentData),
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
        ]);

        $updated = $this->messages->find($messageId);
        return $this->messages->toTelegram($updated);
    }

    /**
     * Edit message reply markup
     */
    public function editReplyMarkup(int|string $chatId, int|string $messageId, ?array $replyMarkup): array
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        $this->messages->editMessage($messageId, [
            'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
        ]);

        $updated = $this->messages->find($messageId);
        return $this->messages->toTelegram($updated);
    }

    /**
     * Edit live location
     */
    public function editLiveLocation(int|string $chatId, int|string $messageId, float $latitude, float $longitude, array $options = []): array
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        $contentData = json_decode($message['content_data'] ?? '{}', true);
        $contentData['latitude'] = $latitude;
        $contentData['longitude'] = $longitude;
        if (isset($options['live_period'])) $contentData['live_period'] = $options['live_period'];
        if (isset($options['heading'])) $contentData['heading'] = $options['heading'];
        if (isset($options['proximity_alert_radius'])) $contentData['proximity_alert_radius'] = $options['proximity_alert_radius'];

        $this->messages->editMessage($messageId, [
            'content_data' => json_encode($contentData),
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
        ]);

        $updated = $this->messages->find($messageId);
        return $this->messages->toTelegram($updated);
    }

    /**
     * Stop live location
     */
    public function stopLiveLocation(int|string $chatId, int|string $messageId, array $options = []): array
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        $contentData = json_decode($message['content_data'] ?? '{}', true);
        unset($contentData['live_period']);

        $this->messages->editMessage($messageId, [
            'content_data' => json_encode($contentData),
            'reply_markup' => isset($options['reply_markup']) ? (is_string($options['reply_markup']) ? $options['reply_markup'] : json_encode($options['reply_markup'])) : null,
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
     * Delete multiple messages
     */
    public function deleteMessages(int|string $chatId, array $messageIds): bool
    {
        $success = true;
        foreach ($messageIds as $id) {
            if (!$this->deleteMessage($chatId, $id)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Forward a message
     */
    public function forwardMessage(int|string $fromChatId, int|string $toChatId, int|string $messageId, int|string $senderId): ?array
    {
        $newMessageId = $this->messages->forwardMessage($fromChatId, $toChatId, $messageId, $senderId);
        if (!$newMessageId) {
            return null;
        }

        $message = $this->messages->find($newMessageId);
        $this->broadcastToChat($toChatId, 'message', $this->messages->toTelegram($message), $senderId);

        return $this->messages->toTelegram($message);
    }

    /**
     * Forward multiple messages
     */
    public function forwardMessages(int|string $fromChatId, int|string $toChatId, array $messageIds, int|string $senderId): array
    {
        $results = [];
        foreach ($messageIds as $id) {
            $result = $this->forwardMessage($fromChatId, $toChatId, $id, $senderId);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    /**
     * Copy a message (forward without attribution)
     */
    public function copyMessage(int|string $fromChatId, int|string $toChatId, int|string $messageId, int|string $senderId): ?array
    {
        $original = $this->messages->getMessage($fromChatId, $messageId);
        if (!$original) {
            return null;
        }

        $messageId = $this->messages->sendMessage([
            'chat_id' => $toChatId,
            'sender_id' => $senderId,
            'text' => $original['text'],
            'caption' => $original['caption'],
            'content_type' => $original['content_type'],
            'content_data' => $original['content_data'],
            'entities' => $original['entities'],
            'caption_entities' => $original['caption_entities'] ?? null,
        ]);

        $message = $this->messages->find($messageId);
        $this->broadcastToChat($toChatId, 'message', $this->messages->toTelegram($message), $senderId);
        return ['message_id' => (int) $message['id']];
    }

    /**
     * Copy multiple messages
     */
    public function copyMessages(int|string $fromChatId, int|string $toChatId, array $messageIds, int|string $senderId): array
    {
        $results = [];
        foreach ($messageIds as $id) {
            $result = $this->copyMessage($fromChatId, $toChatId, $id, $senderId);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    /**
     * Set message reaction
     */
    public function setReaction(int|string $chatId, int|string $messageId, ?array $reaction, bool $isBig = false): bool
    {
        $message = $this->messages->getMessage($chatId, $messageId);
        if (!$message) {
            throw new \InvalidArgumentException('Message not found');
        }

        return $this->messages->upsertReaction($messageId, $reaction, $isBig);
    }

    /**
     * Delete message reaction
     */
    public function deleteReaction(int|string $chatId, int|string $messageId, int|string $userId): bool
    {
        return $this->messages->deleteReaction($messageId, $userId);
    }

    /**
     * Delete all reactions for a message
     */
    public function deleteAllReactions(int|string $chatId, int|string $messageId): bool
    {
        return $this->messages->deleteAllReactions($messageId);
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
     * Verify sender is member of chat
     */
    private function verifyMember(int|string $chatId, int|string $senderId): void
    {
        if (!$this->chats->isMember($chatId, $senderId)) {
            throw new \InvalidArgumentException('User is not a member of this chat');
        }
    }

    /**
     * Broadcast an update to all chat members (except sender)
     */
    private function broadcastToChat(int|string $chatId, string $type, array $payload, int|string|null $excludeUserId = null): void
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
