<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Callback controller — answerCallbackQuery
 */
class CallbackController extends BaseController
{
    public function answerCallbackQuery(Request $request, string $token): Response
    {
        // TODO: Process callback query answer
        return $this->ok(true);
    }
}
