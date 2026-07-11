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
        return $this->ok(['id' => 'prepared_' . md5(random_int(0, PHP_INT_MAX) . time())]);
    }

    /**
     * savePreparedKeyboardButton — Save prepared keyboard button
     */
    public function savePreparedKeyboardButton(Request $request, string $token): Response
    {
        return $this->ok(true);
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

            return $this->ok(['id' => 'webapp_' . md5(random_int(0, PHP_INT_MAX) . time())]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
