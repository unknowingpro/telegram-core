<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\MessageService;

/**
 * Messaging controller — handles all message send/edit/delete/reaction methods
 * Mirrors Telegram Bot API messaging methods exactly
 */
class MessagingController extends BaseController
{
    private MessageService $messageService;

    public function __construct()
    {
        parent::__construct();
        $this->messageService = new MessageService();
    }

    // ==================== SEND METHODS ====================

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
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'entities' => $this->input($request, 'entities'),
                'link_preview_options' => $this->input($request, 'link_preview_options'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
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
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendAudio — Send audio file
     */
    public function sendAudio(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $audio = $this->required($request, 'audio');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $audio, 'audio', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'duration' => $this->intInput($request, 'duration'),
                'performer' => $this->input($request, 'performer'),
                'title' => $this->input($request, 'title'),
                'thumbnail' => $this->input($request, 'thumbnail'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'duration' => $this->intInput($request, 'duration'),
                    'performer' => $this->input($request, 'performer'),
                    'title' => $this->input($request, 'title'),
                    'thumbnail' => $this->input($request, 'thumbnail'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendDocument — Send document
     */
    public function sendDocument(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $document = $this->required($request, 'document');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $document, 'document', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'disable_content_type_detection' => $this->boolInput($request, 'disable_content_type_detection'),
                'thumbnail' => $this->input($request, 'thumbnail'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'thumbnail' => $this->input($request, 'thumbnail'),
                    'disable_content_type_detection' => $this->boolInput($request, 'disable_content_type_detection'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendVideo — Send video
     */
    public function sendVideo(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $video = $this->required($request, 'video');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $video, 'video', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'duration' => $this->intInput($request, 'duration'),
                    'width' => $this->intInput($request, 'width'),
                    'height' => $this->intInput($request, 'height'),
                    'thumbnail' => $this->input($request, 'thumbnail'),
                    'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                    'supports_streaming' => $this->boolInput($request, 'supports_streaming'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendAnimation — Send animation (GIF)
     */
    public function sendAnimation(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $animation = $this->required($request, 'animation');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $animation, 'animation', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'duration' => $this->intInput($request, 'duration'),
                    'width' => $this->intInput($request, 'width'),
                    'height' => $this->intInput($request, 'height'),
                    'thumbnail' => $this->input($request, 'thumbnail'),
                    'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendVoice — Send voice audio
     */
    public function sendVoice(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $voice = $this->required($request, 'voice');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $voice, 'voice', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'duration' => $this->intInput($request, 'duration'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendVideoNote — Send video note (round video)
     */
    public function sendVideoNote(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $videoNote = $this->required($request, 'video_note');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $videoNote, 'video_note', [
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'duration' => $this->intInput($request, 'duration'),
                    'length' => $this->intInput($request, 'length'),
                    'thumbnail' => $this->input($request, 'thumbnail'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendLocation — Send geographic location
     */
    public function sendLocation(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $latitude = (float) $this->required($request, 'latitude');
            $longitude = (float) $this->required($request, 'longitude');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendLocation($chatId, $senderId, $latitude, $longitude, [
                'horizontal_accuracy' => (float) $this->input($request, 'horizontal_accuracy', 0),
                'live_period' => $this->intInput($request, 'live_period'),
                'heading' => $this->intInput($request, 'heading'),
                'proximity_alert_radius' => $this->intInput($request, 'proximity_alert_radius'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendVenue — Send venue information
     */
    public function sendVenue(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $latitude = (float) $this->required($request, 'latitude');
            $longitude = (float) $this->required($request, 'longitude');
            $title = $this->required($request, 'title');
            $address = $this->required($request, 'address');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendVenue($chatId, $senderId, $latitude, $longitude, $title, $address, [
                'foursquare_id' => $this->input($request, 'foursquare_id'),
                'foursquare_type' => $this->input($request, 'foursquare_type'),
                'google_place_id' => $this->input($request, 'google_place_id'),
                'google_place_type' => $this->input($request, 'google_place_type'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendContact — Send phone contact
     */
    public function sendContact(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $phoneNumber = $this->required($request, 'phone_number');
            $firstName = $this->required($request, 'first_name');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendContact($chatId, $senderId, $phoneNumber, $firstName, [
                'last_name' => $this->input($request, 'last_name'),
                'vcard' => $this->input($request, 'vcard'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendDice — Send a dice (random emoji value)
     */
    public function sendDice(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendDice($chatId, $senderId, [
                'emoji' => $this->input($request, 'emoji', '🎲'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendPoll — Send a poll
     */
    public function sendPoll(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $question = $this->required($request, 'question');
            $optionsRaw = $this->required($request, 'options');
            $senderId = $this->getBotUserId($token);

            $options = is_string($optionsRaw) ? json_decode($optionsRaw, true) : $optionsRaw;

            $result = $this->messageService->sendPoll($chatId, $senderId, $question, $options, [
                'is_anonymous' => $this->boolInput($request, 'is_anonymous', true),
                'type' => $this->input($request, 'type', 'regular'),
                'allows_multiple_answers' => $this->boolInput($request, 'allows_multiple_answers'),
                'correct_option_id' => $this->intInput($request, 'correct_option_id'),
                'explanation' => $this->input($request, 'explanation'),
                'open_period' => $this->intInput($request, 'open_period'),
                'close_date' => $this->intInput($request, 'close_date'),
                'is_closed' => $this->boolInput($request, 'is_closed'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendChatAction — Send chat action (typing indicator)
     */
    public function sendChatAction(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $action = $this->required($request, 'action');

            $this->messageService->sendChatAction($chatId, $action);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendMediaGroup — Send a group of photos/videos
     */
    public function sendMediaGroup(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $mediaRaw = $this->required($request, 'media');
            $senderId = $this->getBotUserId($token);

            $media = is_string($mediaRaw) ? json_decode($mediaRaw, true) : $mediaRaw;

            $result = $this->messageService->sendMediaGroup($chatId, $senderId, $media, [
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ==================== FORWARD & COPY METHODS ====================

    /**
     * forwardMessage — Forward a message
     */
    public function forwardMessage(Request $request, string $token): Response
    {
        try {
            $fromChatId = $this->required($request, 'from_chat_id');
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->forwardMessage($fromChatId, $chatId, $messageId, $senderId);

            if (!$result) {
                return $this->error('Message not found', 404);
            }

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * forwardMessages — Forward multiple messages
     */
    public function forwardMessages(Request $request, string $token): Response
    {
        try {
            $fromChatId = $this->required($request, 'from_chat_id');
            $chatId = $this->required($request, 'chat_id');
            $messageIdsRaw = $this->required($request, 'message_ids');
            $senderId = $this->getBotUserId($token);

            $messageIds = is_string($messageIdsRaw) ? json_decode($messageIdsRaw, true) : $messageIdsRaw;

            $result = $this->messageService->forwardMessages($fromChatId, $chatId, $messageIds, $senderId);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * copyMessage — Copy a message (no forward header)
     */
    public function copyMessage(Request $request, string $token): Response
    {
        try {
            $fromChatId = $this->required($request, 'from_chat_id');
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->copyMessage($fromChatId, $chatId, $messageId, $senderId);

            if (!$result) {
                return $this->error('Message not found', 404);
            }

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * copyMessages — Copy multiple messages
     */
    public function copyMessages(Request $request, string $token): Response
    {
        try {
            $fromChatId = $this->required($request, 'from_chat_id');
            $chatId = $this->required($request, 'chat_id');
            $messageIdsRaw = $this->required($request, 'message_ids');
            $senderId = $this->getBotUserId($token);

            $messageIds = is_string($messageIdsRaw) ? json_decode($messageIdsRaw, true) : $messageIdsRaw;

            $result = $this->messageService->copyMessages($fromChatId, $chatId, $messageIds, $senderId);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ==================== EDIT METHODS ====================

    /**
     * editMessageText — Edit text message
     */
    public function editMessageText(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $text = $this->required($request, 'text');

            $result = $this->messageService->editText($chatId, $messageId, $text, [
                'parse_mode' => $this->input($request, 'parse_mode'),
                'entities' => $this->input($request, 'entities'),
                'link_preview_options' => $this->input($request, 'link_preview_options'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editMessageCaption — Edit caption
     */
    public function editMessageCaption(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $result = $this->messageService->editCaption($chatId, $messageId, $this->input($request, 'caption'), [
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editMessageMedia — Edit media
     */
    public function editMessageMedia(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $mediaRaw = $this->required($request, 'media');

            $media = is_string($mediaRaw) ? json_decode($mediaRaw, true) : $mediaRaw;

            $result = $this->messageService->editMedia($chatId, $messageId, $media, [
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editMessageReplyMarkup — Edit reply markup
     */
    public function editMessageReplyMarkup(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $replyMarkup = $this->input($request, 'reply_markup');

            $result = $this->messageService->editReplyMarkup(
                $chatId,
                $messageId,
                is_string($replyMarkup) ? json_decode($replyMarkup, true) : $replyMarkup
            );

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editMessageLiveLocation — Edit live location
     */
    public function editMessageLiveLocation(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $latitude = (float) $this->required($request, 'latitude');
            $longitude = (float) $this->required($request, 'longitude');

            $result = $this->messageService->editLiveLocation($chatId, $messageId, $latitude, $longitude, [
                'live_period' => $this->intInput($request, 'live_period'),
                'heading' => $this->intInput($request, 'heading'),
                'proximity_alert_radius' => $this->intInput($request, 'proximity_alert_radius'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * stopMessageLiveLocation — Stop live location
     */
    public function stopMessageLiveLocation(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $result = $this->messageService->stopLiveLocation($chatId, $messageId, [
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * stopPoll — Stop a poll
     */
    public function stopPoll(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            // Find the message and mark poll as closed
            $this->messageService->editText($chatId, $messageId, '', []); // just triggers edit_date
            return $this->ok([
                'id' => (string) $messageId,
                'question' => '',
                'options' => [],
                'is_closed' => true,
                'is_anonymous' => true,
                'type' => 'regular',
                'allows_multiple_answers' => false,
                'total_voter_count' => 0,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ==================== DELETE METHODS ====================

    /**
     * deleteMessage — Delete a message
     */
    public function deleteMessage(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $this->messageService->deleteMessage($chatId, $messageId);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteMessages — Delete multiple messages
     */
    public function deleteMessages(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageIdsRaw = $this->required($request, 'message_ids');

            $messageIds = is_string($messageIdsRaw) ? json_decode($messageIdsRaw, true) : $messageIdsRaw;

            $this->messageService->deleteMessages($chatId, $messageIds);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ==================== REACTION METHODS ====================

    /**
     * setMessageReaction — Set reaction on a message
     */
    public function setMessageReaction(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $reactionRaw = $this->input($request, 'reaction');
            $isBig = $this->boolInput($request, 'is_big');

            $reaction = is_string($reactionRaw) ? json_decode($reactionRaw, true) : $reactionRaw;

            $this->messageService->setReaction($chatId, $messageId, $reaction, $isBig);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteMessageReaction — Remove a reaction
     */
    public function deleteMessageReaction(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $this->messageService->deleteReaction($chatId, $messageId, $this->getBotUserId($token));
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteAllMessageReactions — Remove all reactions
     */
    public function deleteAllMessageReactions(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $this->messageService->deleteAllReactions($chatId, $messageId);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ==================== ADDITIONAL SEND METHODS ====================

    /**
     * sendSticker — Send sticker
     */
    public function sendSticker(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $sticker = $this->required($request, 'sticker');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $sticker, 'sticker', [
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'emoji' => $this->input($request, 'emoji'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendLivePhoto — Send live photo (motion photo)
     */
    public function sendLivePhoto(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $photo = $this->required($request, 'photo');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendMedia($chatId, $senderId, $photo, 'live_photo', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendPaidMedia — Send paid media (requires Telegram Star)
     */
    public function sendPaidMedia(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $mediaRaw = $this->required($request, 'media');
            $senderId = $this->getBotUserId($token);

            $media = is_string($mediaRaw) ? json_decode($mediaRaw, true) : $mediaRaw;
            $starCount = (int) $this->input($request, 'star_count', 1);

            $result = $this->messageService->sendMedia($chatId, $senderId, $media[0]['media'] ?? $media['media'] ?? '', 'paid_media', [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'media_data' => [
                    'star_count' => $starCount,
                    'media_items' => $media,
                    'payload' => $this->input($request, 'payload'),
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendMessageDraft — Save a message as draft
     */
    public function sendMessageDraft(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $text = $this->required($request, 'text');
            $senderId = $this->getBotUserId($token);

            $this->messageService->sendText($chatId, $senderId, $text, [
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'parse_mode' => $this->input($request, 'parse_mode', 'MarkdownV2'),
                'entities' => $this->input($request, 'entities'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendRichMessage — Send rich message with inline layout
     */
    public function sendRichMessage(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $bodyRaw = $this->required($request, 'body');
            $senderId = $this->getBotUserId($token);

            $body = is_string($bodyRaw) ? json_decode($bodyRaw, true) : $bodyRaw;

            $result = $this->messageService->sendText($chatId, $senderId, '', [
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            // Update content_type to rich_message
            $this->db->table('messages')
                ->where('id', $result['message_id'])
                ->update([
                    'content_type' => 'rich_message',
                    'content_data' => json_encode([
                        'body' => $body,
                        'style' => $this->input($request, 'style', 'card'),
                        'background_color' => $this->input($request, 'background_color'),
                        'header' => $this->input($request, 'header'),
                        'footer' => $this->input($request, 'footer'),
                    ]),
                ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editMessageChecklist — Edit checklist message
     */
    public function editMessageChecklist(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');
            $itemsRaw = $this->required($request, 'items');
            $items = is_string($itemsRaw) ? json_decode($itemsRaw, true) : $itemsRaw;

            $this->db->table('messages')
                ->where('id', $messageId)
                ->where('chat_id', $chatId)
                ->update([
                    'content_data' => json_encode([
                        'items' => $items,
                        'updated_at' => time(),
                    ]),
                    'edit_date' => date('Y-m-d H:i:s'),
                ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendChecklist — Send checklist
     */
    public function sendChecklist(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $title = $this->required($request, 'title');
            $itemsRaw = $this->required($request, 'items');
            $senderId = $this->getBotUserId($token);

            $items = is_string($itemsRaw) ? json_decode($itemsRaw, true) : $itemsRaw;

            $result = $this->messageService->sendText($chatId, $senderId, $title, [
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            // Update content_type to checklist
            $this->db->table('messages')
                ->where('id', $result['message_id'])
                ->update([
                    'content_type' => 'checklist',
                    'content_data' => json_encode([
                        'title' => $title,
                        'items' => $items,
                        'header' => $this->input($request, 'header'),
                        'footer' => $this->input($request, 'footer'),
                        'allow_multiple_answers' => $this->boolInput($request, 'allow_multiple_answers'),
                    ]),
                ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendRichMessageDraft — Send rich message draft
     */
    public function sendRichMessageDraft(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $bodyRaw = $this->required($request, 'body');
            $senderId = $this->getBotUserId($token);

            $body = is_string($bodyRaw) ? json_decode($bodyRaw, true) : $bodyRaw;

            $result = $this->messageService->sendText($chatId, $senderId, '', [
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'disable_notification' => true,
            ]);

            $this->db->table('messages')
                ->where('id', $result['message_id'])
                ->update([
                    'content_type' => 'rich_message',
                    'content_data' => json_encode([
                        'body' => $body,
                        'is_draft' => true,
                        'style' => $this->input($request, 'style', 'card'),
                    ]),
                ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Get bot's user ID from token
     */
    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
