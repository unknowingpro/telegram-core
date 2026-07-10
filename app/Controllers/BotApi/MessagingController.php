<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\MessageService;

/**
 * Messaging controller — handles all message send/edit/delete methods
 * Mirrors Telegram Bot API messaging methods
 */
class MessagingController extends BaseController
{
    private MessageService $messageService;

    public function __construct()
    {
        parent::__construct();
        $this->messageService = new MessageService();
    }

    /**
     * sendMessage — Send text message
     */
    public function sendMessage(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $text = $this->required($request, 'text');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendText($chatId, $senderId, $text, [
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'entities' => $this->input($request, 'entities'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * sendPhoto — Send photo
     */
    public function sendPhoto(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $photo = $this->required($request, 'photo');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendPhoto($chatId, $senderId, $photo, [
                'caption' => $this->input($request, 'caption'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * editMessageText — Edit text message
     */
    public function editMessageText(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $text = $this->required($request, 'text');

            $result = $this->messageService->editText($chatId, $messageId, $text);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * deleteMessage — Delete a message
     */
    public function deleteMessage(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $result = $this->messageService->deleteMessage($chatId, $messageId);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * forwardMessage — Forward a message
     */
    public function forwardMessage(Request $request, string $token): Response
    {
        try {
            $fromChatId = $this->required($request, 'from_chat_id');
            $toChatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->forwardMessage($fromChatId, $toChatId, $messageId, $senderId);

            if (!$result) {
                return $this->error('Message not found', 404);
            }

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * copyMessage — Copy message (no forward header)
     */
    public function copyMessage(Request $request, string $token): Response
    {
        try {
            $fromChatId = $this->required($request, 'from_chat_id');
            $toChatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->forwardMessage($fromChatId, $toChatId, $messageId, $senderId);

            return $this->ok(['message_id' => $result['message_id'] ?? null]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Stub methods for all other messaging operations
     * These return ok:true to maintain API compatibility
     */
    public function forwardMessages(Request $request, string $token): Response { return $this->ok(true); }
    public function copyMessages(Request $request, string $token): Response { return $this->ok(true); }
    public function sendAudio(Request $request, string $token): Response { return $this->ok(true); }
    public function sendDocument(Request $request, string $token): Response { return $this->ok(true); }
    public function sendVideo(Request $request, string $token): Response { return $this->ok(true); }
    public function sendAnimation(Request $request, string $token): Response { return $this->ok(true); }
    public function sendVoice(Request $request, string $token): Response { return $this->ok(true); }
    public function sendVideoNote(Request $request, string $token): Response { return $this->ok(true); }
    public function sendLocation(Request $request, string $token): Response { return $this->ok(true); }
    public function sendVenue(Request $request, string $token): Response { return $this->ok(true); }
    public function sendContact(Request $request, string $token): Response { return $this->ok(true); }
    public function sendDice(Request $request, string $token): Response { return $this->ok(true); }
    public function sendPoll(Request $request, string $token): Response { return $this->ok(true); }
    public function sendChecklist(Request $request, string $token): Response { return $this->ok(true); }
    public function sendChatAction(Request $request, string $token): Response { return $this->ok(true); }
    public function sendMediaGroup(Request $request, string $token): Response { return $this->ok(true); }
    public function sendMessageDraft(Request $request, string $token): Response { return $this->ok(true); }
    public function sendRichMessage(Request $request, string $token): Response { return $this->ok(true); }
    public function editMessageCaption(Request $request, string $token): Response { return $this->ok(true); }
    public function editMessageMedia(Request $request, string $token): Response { return $this->ok(true); }
    public function editMessageReplyMarkup(Request $request, string $token): Response { return $this->ok(true); }
    public function editMessageLiveLocation(Request $request, string $token): Response { return $this->ok(true); }
    public function stopMessageLiveLocation(Request $request, string $token): Response { return $this->ok(true); }
    public function editMessageChecklist(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteMessages(Request $request, string $token): Response { return $this->ok(true); }
    public function setMessageReaction(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteMessageReaction(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteAllMessageReactions(Request $request, string $token): Response { return $this->ok(true); }

    /**
     * Get bot's user ID from token (simplified — in production, look up bot in DB)
     */
    private function getBotUserId(string $token): int
    {
        // TODO: Look up bot user by token in database
        // For now, use token hash as temporary ID
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
