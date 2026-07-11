<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Stars controller — Telegram Stars methods
 * Mirrors Telegram Bot API star methods
 */
class StarsController extends BaseController
{
    public function getMyStarBalance(Request $request, string $token): Response
    {
        return $this->ok(['balance' => 0]);
    }

    public function getStarTransactions(Request $request, string $token): Response
    {
        return $this->ok(['transactions' => []]);
    }

    public function refundStarPayment(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function editUserStarSubscription(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function giftPremiumSubscription(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function sendGift(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getAvailableGifts(Request $request, string $token): Response
    {
        return $this->ok(['gifts' => []]);
    }

    public function getChatGifts(Request $request, string $token): Response
    {
        return $this->ok(['gifts' => []]);
    }

    public function getUserGifts(Request $request, string $token): Response
    {
        return $this->ok(['gifts' => []]);
    }

    public function convertGiftToStars(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function transferGift(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function upgradeGift(Request $request, string $token): Response
    {
        return $this->ok(true);
    }
}
