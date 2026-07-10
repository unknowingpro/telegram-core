<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Inline controller — inline queries, web app queries
 */
class InlineController extends BaseController
{
    public function answerInlineQuery(Request $request, string $token): Response { return $this->ok(true); }
    public function savePreparedInlineMessage(Request $request, string $token): Response { return $this->ok(true); }
    public function savePreparedKeyboardButton(Request $request, string $token): Response { return $this->ok(true); }
    public function answerWebAppQuery(Request $request, string $token): Response { return $this->ok(true); }
}
