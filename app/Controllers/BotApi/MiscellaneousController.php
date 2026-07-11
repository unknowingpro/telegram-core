<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Miscellaneous controller — methods that don't fit other categories
 * Mirrors Telegram Bot API misc methods
 */
class MiscellaneousController extends BaseController
{
    public function answerChatJoinRequestQuery(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function answerGuestQuery(Request $request, string $token): Response
    {
        return $this->ok(['id' => 'guest_' . md5(time())]);
    }

    public function approveSuggestedPost(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function declineSuggestedPost(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function banChatSenderChat(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function unbanChatSenderChat(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function createChatSubscriptionInviteLink(Request $request, string $token): Response
    {
        return $this->ok([
            'invite_link' => 'https://t.me/+' . md5(random_int(0, PHP_INT_MAX)),
            'creator' => $this->getBotUserId($token),
            'creates_join_request' => false,
            'is_primary' => false,
            'is_revoked' => false,
        ]);
    }

    public function editChatSubscriptionInviteLink(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function deleteStickerSet(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getManagedBotAccessSettings(Request $request, string $token): Response
    {
        return $this->ok(['permissions' => []]);
    }

    public function setManagedBotAccessSettings(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getManagedBotToken(Request $request, string $token): Response
    {
        return $this->ok(['token' => $token]);
    }

    public function replaceManagedBotToken(Request $request, string $token): Response
    {
        return $this->ok(['token' => 'new_' . md5(time())]);
    }

    public function setChatMemberTag(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getUserPersonalChatMessages(Request $request, string $token): Response
    {
        return $this->ok(['messages' => []]);
    }

    public function getUserChatBoosts(Request $request, string $token): Response
    {
        return $this->ok(['boosts' => []]);
    }

    public function getBusinessAccountGifts(Request $request, string $token): Response
    {
        return $this->ok(['gifts' => []]);
    }

    public function sendChatJoinRequestWebApp(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
