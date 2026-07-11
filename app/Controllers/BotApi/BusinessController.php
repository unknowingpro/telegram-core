<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Business controller — business account management
 * Mirrors Telegram Bot API business methods
 */
class BusinessController extends BaseController
{
    public function setBusinessAccountName(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function setBusinessAccountUsername(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function setBusinessAccountBio(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function setBusinessAccountProfilePhoto(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function removeBusinessAccountProfilePhoto(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function setBusinessAccountGiftSettings(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getBusinessConnection(Request $request, string $token): Response
    {
        return $this->ok([
            'id' => 'biz_' . md5(time()),
            'user' => ['id' => 0, 'is_bot' => false, 'first_name' => 'Business'],
            'user_chat_id' => 0,
            'date' => time(),
        ]);
    }

    public function readBusinessMessage(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function deleteBusinessMessages(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getBusinessAccountStarBalance(Request $request, string $token): Response
    {
        return $this->ok(['balance' => 0]);
    }

    public function transferBusinessAccountStars(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getBusinessAccountGifts(Request $request, string $token): Response
    {
        return $this->ok(['gifts' => []]);
    }
}
