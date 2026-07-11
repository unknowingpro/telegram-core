<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Passport controller — setPassportDataErrors
 * Mirrors Telegram Bot API passport methods exactly
 */
class PassportController extends BaseController
{
    /**
     * setPassportDataErrors — Set passport data errors
     */
    public function setPassportDataErrors(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $errorsRaw = $this->required($request, 'errors');
            $errors = is_string($errorsRaw) ? json_decode($errorsRaw, true) : $errorsRaw;

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
