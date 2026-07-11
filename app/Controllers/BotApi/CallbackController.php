<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Callback controller — answerCallbackQuery
 * Mirrors Telegram Bot API callback methods exactly
 */
class CallbackController extends BaseController
{
    /**
     * answerCallbackQuery — Answer callback query from inline keyboard
     */
    public function answerCallbackQuery(Request $request, string $token): Response
    {
        try {
            $callbackQueryId = $this->required($request, 'callback_query_id');
            $text = $this->input($request, 'text');
            $showAlert = $this->boolInput($request, 'show_alert');
            $url = $this->input($request, 'url');
            $cacheTime = $this->intInput($request, 'cache_time', 0);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
