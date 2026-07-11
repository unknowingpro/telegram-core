<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Inline controller — inline queries, web app queries
 * Mirrors Telegram Bot API inline methods exactly
 */
class InlineController extends BaseController
{
    /**
     * answerInlineQuery — Answer inline query
     */
    public function answerInlineQuery(Request $request, string $token): Response
    {
        try {
            $inlineQueryId = $this->required($request, 'inline_query_id');
            $resultsRaw = $this->required($request, 'results');
            $results = is_string($resultsRaw) ? json_decode($resultsRaw, true) : $resultsRaw;
            $nextOffset = $this->input($request, 'next_offset');
            $buttonRaw = $this->input($request, 'button');
            $button = $buttonRaw ? (is_string($buttonRaw) ? json_decode($buttonRaw, true) : $buttonRaw) : null;
            $isPersonal = $this->boolInput($request, 'is_personal');

            // Store the answered inline query for audit
            $this->db->table('inline_queries')->insert([
                'user_id' => $this->getBotId($token),
                'query' => json_encode($results),
                'offset_val' => $nextOffset,
                'chat_type' => $this->input($request, 'chat_type'),
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * savePreparedInlineMessage — Save prepared inline message
     */
    public function savePreparedInlineMessage(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $resultRaw = $this->required($request, 'result');
            $result = is_string($resultRaw) ? json_decode($resultRaw, true) : $resultRaw;

            $id = 'prepared_' . bin2hex(random_bytes(8));

            $this->db->table('inline_queries')->insert([
                'user_id' => $userId,
                'query' => json_encode(['type' => 'prepared_inline', 'result' => $result, 'prepared_id' => $id]),
                'offset_val' => $this->input($request, 'allow_user_chats'),
                'chat_type' => 'prepared',
            ]);

            return $this->ok(['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * savePreparedKeyboardButton — Save prepared keyboard button
     */
    public function savePreparedKeyboardButton(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $buttonRaw = $this->required($request, 'keyboard_button');
            $button = is_string($buttonRaw) ? json_decode($buttonRaw, true) : $buttonRaw;
            if (!is_array($button)) {
                throw new \InvalidArgumentException('keyboard_button must be a valid JSON object');
            }

            $id = 'keyboard_' . bin2hex(random_bytes(8));

            $this->db->table('inline_queries')->insert([
                'user_id' => $userId,
                'query' => json_encode([
                    'type' => 'prepared_keyboard',
                    'button' => $button,
                    'prepared_id' => $id,
                    'request_id' => $this->input($request, 'request_id'),
                    'request_data' => $this->input($request, 'request_data'),
                ]),
                'chat_type' => 'prepared_keyboard',
            ]);

            return $this->ok(['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * answerWebAppQuery — Answer web app query
     */
    public function answerWebAppQuery(Request $request, string $token): Response
    {
        try {
            $webAppQueryId = $this->required($request, 'web_app_query_id');
            $resultRaw = $this->required($request, 'result');
            $result = is_string($resultRaw) ? json_decode($resultRaw, true) : $resultRaw;

            $id = 'webapp_' . md5($webAppQueryId . time());

            $this->db->table('inline_queries')->insert([
                'user_id' => $this->getBotId($token),
                'query' => json_encode(['type' => 'webapp_query', 'result' => $result, 'web_app_query_id' => $webAppQueryId]),
                'chat_type' => 'webapp',
            ]);

            return $this->ok(['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    }
