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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
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
            $senderId = $this->getBotUserId($token);

            // Handle file upload or string file_id
            $photo = $this->resolveFileUpload($request, 'photo', $senderId);
            if ($photo === null) {
                $photo = $this->required($request, 'photo');
            }

            $result = $this->messageService->sendPhoto($chatId, $senderId, $photo, [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $audio = $this->resolveFileUpload($request, 'audio', $senderId);
            if ($audio === null) {
                $audio = $this->required($request, 'audio');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $audio, 'audio', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'duration' => $this->intInput($request, 'duration'),
                'performer' => $this->input($request, 'performer'),
                'title' => $this->input($request, 'title'),
                'thumbnail' => $this->input($request, 'thumbnail'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $document = $this->resolveFileUpload($request, 'document', $senderId);
            if ($document === null) {
                $document = $this->required($request, 'document');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $document, 'document', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'disable_content_type_detection' => $this->boolInput($request, 'disable_content_type_detection'),
                'thumbnail' => $this->input($request, 'thumbnail'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $video = $this->resolveFileUpload($request, 'video', $senderId);
            if ($video === null) {
                $video = $this->required($request, 'video');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $video, 'video', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $animation = $this->resolveFileUpload($request, 'animation', $senderId);
            if ($animation === null) {
                $animation = $this->required($request, 'animation');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $animation, 'animation', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $voice = $this->resolveFileUpload($request, 'voice', $senderId);
            if ($voice === null) {
                $voice = $this->required($request, 'voice');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $voice, 'voice', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $videoNote = $this->resolveFileUpload($request, 'video_note', $senderId);
            if ($videoNote === null) {
                $videoNote = $this->required($request, 'video_note');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $videoNote, 'video_note', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'horizontal_accuracy' => (float) $this->input($request, 'horizontal_accuracy', 0),
                'live_period' => $this->intInput($request, 'live_period'),
                'heading' => $this->intInput($request, 'heading'),
                'proximity_alert_radius' => $this->intInput($request, 'proximity_alert_radius'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'foursquare_id' => $this->input($request, 'foursquare_id'),
                'foursquare_type' => $this->input($request, 'foursquare_type'),
                'google_place_id' => $this->input($request, 'google_place_id'),
                'google_place_type' => $this->input($request, 'google_place_type'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'last_name' => $this->input($request, 'last_name'),
                'vcard' => $this->input($request, 'vcard'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
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
     * sendDice — Send a dice (random emoji value)
     */
    public function sendDice(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendDice($chatId, $senderId, [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'emoji' => $this->input($request, 'emoji', '🎲'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
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
                // Basic poll options
                'is_anonymous' => $this->boolInput($request, 'is_anonymous', true),
                'type' => $this->input($request, 'type', 'regular'),
                'allows_multiple_answers' => $this->boolInput($request, 'allows_multiple_answers'),
                'correct_option_id' => $this->intInput($request, 'correct_option_id'),
                'correct_option_ids' => $this->input($request, 'correct_option_ids'),
                'explanation' => $this->input($request, 'explanation'),
                'explanation_parse_mode' => $this->input($request, 'explanation_parse_mode'),
                'explanation_media' => $this->input($request, 'explanation_media'),
                'open_period' => $this->intInput($request, 'open_period'),
                'close_date' => $this->intInput($request, 'close_date'),
                'is_closed' => $this->boolInput($request, 'is_closed'),

                // Question-level formatting
                'question_parse_mode' => $this->input($request, 'question_parse_mode'),
                'question_entities' => $this->input($request, 'question_entities'),

                // Advanced poll features
                'allows_revoting' => $this->boolInput($request, 'allows_revoting'),
                'shuffle_options' => $this->boolInput($request, 'shuffle_options'),
                'allow_adding_options' => $this->boolInput($request, 'allow_adding_options'),
                'hide_results_until_closes' => $this->boolInput($request, 'hide_results_until_closes'),
                'members_only' => $this->boolInput($request, 'members_only'),
                'country_codes' => $this->input($request, 'country_codes'),

                // Description
                'description' => $this->input($request, 'description'),
                'description_parse_mode' => $this->input($request, 'description_parse_mode'),
                'description_entities' => $this->input($request, 'description_entities'),

                // Media attachment option
                'media' => $this->input($request, 'media'),

                // Messaging options
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
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

            // Process file uploads within the media group
            foreach ($media as $i => $item) {
                $field = 'media_' . $i . '_file';
                $uploaded = $this->resolveFileUpload($request, $field, $senderId);
                if ($uploaded !== null) {
                    $media[$i]['media'] = $uploaded;
                } elseif (isset($item['media'])) {
                    $attachResult = $this->resolveFileUpload($request, $item['media'], $senderId);
                    if ($attachResult !== null) {
                        $media[$i]['media'] = $attachResult;
                    }
                }
            }

            $result = $this->messageService->sendMediaGroup($chatId, $senderId, $media, [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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

            $result = $this->messageService->forwardMessage($fromChatId, $chatId, $messageId, $senderId, [
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
            ]);

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

            $result = $this->messageService->forwardMessages($fromChatId, $chatId, $messageIds, $senderId, [
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
            ]);

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

            $result = $this->messageService->copyMessage($fromChatId, $chatId, $messageId, $senderId, [
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

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

            $result = $this->messageService->copyMessages($fromChatId, $chatId, $messageIds, $senderId, [
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // ==================== EDIT METHODS ====================

    /**
     * Resolve message target: supports both (chat_id + message_id) and inline_message_id.
     * Returns [chatId, messageId] or throws if neither is provided.
     */
    private function resolveMessageTarget(Request $request): array
    {
        $inlineMessageId = $this->input($request, 'inline_message_id');
        if ($inlineMessageId !== null) {
            // Inline messages use a composite ID — decode it
            $parts = explode(':', $inlineMessageId, 3);
            return [
                $parts[0] ?? 0,
                $parts[1] ?? 0,
            ];
        }

        return [
            $this->required($request, 'chat_id'),
            $this->required($request, 'message_id'),
        ];
    }

    /**
     * Get common optional messaging parameters for edit methods
     */
    private function getEditOptions(Request $request): array
    {
        return [
            'business_connection_id' => $this->input($request, 'business_connection_id'),
            'inline_message_id' => $this->input($request, 'inline_message_id'),
        ];
    }

    /**
     * Global optional parameters applicable to all send/forward/copy methods.
     * Merged into every send* method's options array.
     */
    private function getSendGlobals(Request $request): array
    {
        return [
            'business_connection_id' => $this->input($request, 'business_connection_id'),
            'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
            'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
            'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
            'message_effect_id' => $this->input($request, 'message_effect_id'),
            'reply_parameters' => $this->input($request, 'reply_parameters'),
            'disable_notification' => $this->boolInput($request, 'disable_notification'),
            'protect_content' => $this->boolInput($request, 'protect_content'),
            'message_thread_id' => $this->input($request, 'message_thread_id'),
        ];
    }

    /**
     * editMessageText — Edit text message
     */
    public function editMessageText(Request $request, string $token): Response
    {
        try {
            [$chatId, $messageId] = $this->resolveMessageTarget($request);
            $text = $this->required($request, 'text');

            $result = $this->messageService->editText($chatId, $messageId, $text, [
                'parse_mode' => $this->input($request, 'parse_mode'),
                'entities' => $this->input($request, 'entities'),
                'link_preview_options' => $this->input($request, 'link_preview_options'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'business_connection_id' => $this->input($request, 'business_connection_id'),
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
            [$chatId, $messageId] = $this->resolveMessageTarget($request);

            $result = $this->messageService->editCaption($chatId, $messageId, $this->input($request, 'caption'), [
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'business_connection_id' => $this->input($request, 'business_connection_id'),
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
            [$chatId, $messageId] = $this->resolveMessageTarget($request);
            $mediaRaw = $this->required($request, 'media');

            $media = is_string($mediaRaw) ? json_decode($mediaRaw, true) : $mediaRaw;

            $result = $this->messageService->editMedia($chatId, $messageId, $media, [
                'reply_markup' => $this->input($request, 'reply_markup'),
                'business_connection_id' => $this->input($request, 'business_connection_id'),
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
            [$chatId, $messageId] = $this->resolveMessageTarget($request);
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
            [$chatId, $messageId] = $this->resolveMessageTarget($request);
            $latitude = (float) $this->required($request, 'latitude');
            $longitude = (float) $this->required($request, 'longitude');

            $result = $this->messageService->editLiveLocation($chatId, $messageId, $latitude, $longitude, [
                'live_period' => $this->intInput($request, 'live_period'),
                'heading' => $this->intInput($request, 'heading'),
                'proximity_alert_radius' => $this->intInput($request, 'proximity_alert_radius'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'business_connection_id' => $this->input($request, 'business_connection_id'),
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
            [$chatId, $messageId] = $this->resolveMessageTarget($request);

            $result = $this->messageService->stopLiveLocation($chatId, $messageId, [
                'reply_markup' => $this->input($request, 'reply_markup'),
                'business_connection_id' => $this->input($request, 'business_connection_id'),
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
            [$chatId, $messageId] = $this->resolveMessageTarget($request);

            // Find the message and mark poll as closed
            $this->messageService->editText($chatId, $messageId, '', []);
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
            $senderId = $this->getBotUserId($token);

            $sticker = $this->resolveFileUpload($request, 'sticker', $senderId);
            if ($sticker === null) {
                $sticker = $this->required($request, 'sticker');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $sticker, 'sticker', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
            $senderId = $this->getBotUserId($token);

            $photo = $this->resolveFileUpload($request, 'photo', $senderId);
            if ($photo === null) {
                $photo = $this->required($request, 'photo');
            }

            $result = $this->messageService->sendMedia($chatId, $senderId, $photo, 'live_photo', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'has_spoiler' => $this->boolInput($request, 'has_spoiler'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
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
     * sendPaidMedia — Send paid media (requires Telegram Star)
     */
    public function sendPaidMedia(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $mediaRaw = $this->required($request, 'media');
            $senderId = $this->getBotUserId($token);

            $media = is_string($mediaRaw) ? json_decode($mediaRaw, true) : $mediaRaw;

            // Process file uploads in the media array
            foreach ($media as $i => $item) {
                $field = 'media_' . $i . '_file';
                $uploaded = $this->resolveFileUpload($request, $field, $senderId);
                if ($uploaded !== null) {
                    $media[$i]['media'] = $uploaded;
                } elseif (isset($item['media'])) {
                    // Check attach:// protocol
                    $attachResult = $this->resolveFileUpload($request, $item['media'], $senderId);
                    if ($attachResult !== null) {
                        $media[$i]['media'] = $attachResult;
                    }
                }
            }

            $starCount = (int) $this->input($request, 'star_count', 1);
            $payload = $this->input($request, 'payload');

            $result = $this->messageService->sendMedia($chatId, $senderId, $media[0]['media'] ?? $media['media'] ?? '', 'paid_media', [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'caption' => $this->input($request, 'caption'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'caption_entities' => $this->input($request, 'caption_entities'),
                'show_caption_above_media' => $this->boolInput($request, 'show_caption_above_media'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
                'reply_markup' => $this->input($request, 'reply_markup'),
                'payload' => $payload,
                'media_data' => [
                    'star_count' => $starCount,
                    'media_items' => $media,
                    'payload' => $payload,
                ],
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendMessageDraft — Save a message as draft (without notifying users)
     */
    public function sendMessageDraft(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $text = $this->required($request, 'text');
            $senderId = $this->getBotUserId($token);

            $result = $this->messageService->sendText($chatId, $senderId, $text, [
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'parse_mode' => $this->input($request, 'parse_mode', 'MarkdownV2'),
                'entities' => $this->input($request, 'entities'),
                'link_preview_options' => $this->input($request, 'link_preview_options'),
                'disable_notification' => true,
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
                'reply_markup' => $this->input($request, 'reply_markup'),
            ]);

            // Mark as draft
            $this->db->table('messages')
                ->where('id', $result['message_id'])
                ->update([
                    'content_type' => 'draft',
                    'content_data' => json_encode([
                        'is_draft' => true,
                        'original_text' => $text,
                        'entities' => $this->input($request, 'entities'),
                        'link_preview_options' => $this->input($request, 'link_preview_options'),
                    ]),
                ]);

            return $this->ok($result);
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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'reply_to_message_id' => $this->input($request, 'reply_to_message_id'),
                'disable_notification' => $this->boolInput($request, 'disable_notification'),
                'protect_content' => $this->boolInput($request, 'protect_content'),
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
                'business_connection_id' => $this->input($request, 'business_connection_id'),
                'direct_messages_topic_id' => $this->input($request, 'direct_messages_topic_id'),
                'suggested_post_parameters' => $this->input($request, 'suggested_post_parameters'),
                'message_thread_id' => $this->input($request, 'message_thread_id'),
                'disable_notification' => true,
                'allow_paid_broadcast' => $this->boolInput($request, 'allow_paid_broadcast'),
                'message_effect_id' => $this->input($request, 'message_effect_id'),
                'reply_parameters' => $this->input($request, 'reply_parameters'),
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
