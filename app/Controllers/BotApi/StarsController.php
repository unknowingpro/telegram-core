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
    /**
     * getMyStarBalance — Get bot's star balance
     */
    public function getMyStarBalance(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);

        $balance = $this->db->table('star_transactions')
            ->where('user_id', $botId)
            ->select('COALESCE(SUM(amount), 0) as balance')
            ->first();

        return $this->ok(['balance' => (int) ($balance['balance'] ?? 0)]);
    }

    /**
     * getStarTransactions — Get star transaction history
     */
    public function getStarTransactions(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);
        $offset = $this->intInput($request, 'offset', 0);
        $limit = min($this->intInput($request, 'limit', 100), 100);

        $transactions = $this->db->table('star_transactions')
            ->where('user_id', $botId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $result = array_map(fn($t) => [
            'id' => (string) $t['id'],
            'amount' => (int) $t['amount'],
            'type' => $t['type'],
            'description' => $t['description'],
            'date' => strtotime($t['created_at']),
        ], $transactions);

        return $this->ok(['transactions' => $result]);
    }

    /**
     * refundStarPayment — Refund star payment
     */
    public function refundStarPayment(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $telegramPaymentChargeId = $this->required($request, 'telegram_payment_charge_id');
            $botId = $this->getBotId($token);

            $this->db->table('star_transactions')->insert([
                'user_id' => $botId,
                'amount' => 0, // refund amount from original charge
                'type' => 'refund',
                'description' => 'Refund for charge ' . $telegramPaymentChargeId,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editUserStarSubscription — Edit user's star subscription
     */
    public function editUserStarSubscription(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $telegramPaymentChargeId = $this->required($request, 'telegram_payment_charge_id');
            $isCanceled = $this->boolInput($request, 'is_canceled');
            $botId = $this->getBotUserId($token);

            $this->db->table('star_transactions')->insert([
                'user_id' => $botId,
                'amount' => 0,
                'type' => $isCanceled ? 'refund' : 'charge',
                'description' => 'Subscription ' . ($isCanceled ? 'canceled' : 'updated') . ' for charge ' . $telegramPaymentChargeId,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * giftPremiumSubscription — Gift a premium subscription
     */
    public function giftPremiumSubscription(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $isPaid = $this->boolInput($request, 'is_paid');
            $botId = $this->getBotId($token);

            if ($isPaid) {
                $this->db->table('star_transactions')->insert([
                    'user_id' => $botId,
                    'amount' => -1000, // placeholder amount
                    'type' => 'charge',
                    'description' => 'Premium subscription gift for user ' . $userId,
                ]);
            }

            $this->db->table('user_gifts')->insert([
                'user_id' => $userId,
                'gift_id' => 'premium_' . bin2hex(random_bytes(8)),
                'text' => $this->input($request, 'text'),
                'emoji' => '⭐',
                'can_be_transferred' => false,
                'can_be_upgraded' => false,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendGift — Send a gift to a user
     */
    public function sendGift(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $giftId = $this->required($request, 'gift_id');
            $botId = $this->getBotId($token);

            $this->db->table('user_gifts')->insert([
                'user_id' => $userId,
                'gift_id' => $giftId,
                'text' => $this->input($request, 'text'),
                'emoji' => $this->input($request, 'emoji'),
                'is_upgraded' => false,
                'can_be_transferred' => $this->boolInput($request, 'can_be_transferred', true),
                'can_be_upgraded' => $this->boolInput($request, 'can_be_upgraded', true),
            ]);

            $this->db->table('star_transactions')->insert([
                'user_id' => $botId,
                'amount' => -100, // placeholder star cost
                'type' => 'charge',
                'description' => 'Gift ' . $giftId . ' sent to user ' . $userId,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getAvailableGifts — Get list of available gifts
     */
    public function getAvailableGifts(Request $request, string $token): Response
    {
        // Return a curated list of available Telegram gifts
        return $this->ok([
            'gifts' => [
                [
                    'id' => 'gift_flower',
                    'title' => 'Flower',
                    'emoji' => '🌸',
                    'price' => 50,
                    'can_be_transferred' => true,
                    'can_be_upgraded' => true,
                ],
                [
                    'id' => 'gift_heart',
                    'title' => 'Heart',
                    'emoji' => '❤️',
                    'price' => 75,
                    'can_be_transferred' => true,
                    'can_be_upgraded' => true,
                ],
                [
                    'id' => 'gift_star',
                    'title' => 'Star',
                    'emoji' => '⭐',
                    'price' => 100,
                    'can_be_transferred' => true,
                    'can_be_upgraded' => true,
                ],
                [
                    'id' => 'gift_cake',
                    'title' => 'Cake',
                    'emoji' => '🎂',
                    'price' => 200,
                    'can_be_transferred' => true,
                    'can_be_upgraded' => true,
                ],
                [
                    'id' => 'gift_ring',
                    'title' => 'Ring',
                    'emoji' => '💍',
                    'price' => 500,
                    'can_be_transferred' => true,
                    'can_be_upgraded' => false,
                ],
            ],
        ]);
    }

    /**
     * getChatGifts — Get gifts sent to a chat
     */
    public function getChatGifts(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $botId = $this->getBotId($token);

        $gifts = $this->db->table('user_gifts')
            ->where('user_id', $botId)
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $result = array_map(fn($g) => [
            'id' => (string) $g['id'],
            'gift_id' => $g['gift_id'],
            'text' => $g['text'],
            'emoji' => $g['emoji'],
            'is_upgraded' => (bool) $g['is_upgraded'],
            'can_be_transferred' => (bool) $g['can_be_transferred'],
            'can_be_upgraded' => (bool) $g['can_be_upgraded'],
            'date' => strtotime($g['created_at']),
        ], $gifts);

        return $this->ok(['gifts' => $result]);
    }

    /**
     * getUserGifts — Get user's received gifts
     */
    public function getUserGifts(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');

            $gifts = $this->db->table('user_gifts')
                ->where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->limit(50)
                ->get();

            $result = array_map(fn($g) => [
                'id' => (string) $g['id'],
                'gift_id' => $g['gift_id'],
                'text' => $g['text'],
                'emoji' => $g['emoji'],
                'is_upgraded' => (bool) $g['is_upgraded'],
                'can_be_transferred' => (bool) $g['can_be_transferred'],
                'can_be_upgraded' => (bool) $g['can_be_upgraded'],
                'date' => strtotime($g['created_at']),
            ], $gifts);

            return $this->ok(['gifts' => $result]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * convertGiftToStars — Convert a gift to Telegram Stars
     */
    public function convertGiftToStars(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $giftId = $this->required($request, 'gift_id');

            $gift = $this->db->table('user_gifts')
                ->where('id', $giftId)
                ->where('user_id', $userId)
                ->first();

            if (!$gift) {
                return $this->error('Gift not found', 404);
            }

            // Calculate conversion value (e.g., 70% of original price)
            $starValue = 70;
            $botId = $this->getBotId($token);

            $this->db->table('star_transactions')->insert([
                'user_id' => $userId,
                'amount' => $starValue,
                'type' => 'purchase',
                'description' => 'Conversion of gift ' . $gift['gift_id'] . ' to stars',
            ]);

            $this->db->table('star_transactions')->insert([
                'user_id' => $botId,
                'amount' => -$starValue,
                'type' => 'charge',
                'description' => 'Gift conversion fee for ' . $gift['gift_id'],
            ]);

            // Remove the converted gift
            $this->db->table('user_gifts')
                ->where('id', $giftId)
                ->delete();

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * transferGift — Transfer a gift to another user
     */
    public function transferGift(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $newOwnerId = $this->required($request, 'new_owner_id');
            $giftId = $this->required($request, 'gift_id');

            $gift = $this->db->table('user_gifts')
                ->where('id', $giftId)
                ->where('user_id', $userId)
                ->first();

            if (!$gift) {
                return $this->error('Gift not found', 404);
            }

            if (!$gift['can_be_transferred']) {
                return $this->error('Gift cannot be transferred', 400);
            }

            $this->db->table('user_gifts')
                ->where('id', $giftId)
                ->update(['user_id' => $newOwnerId]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * upgradeGift — Upgrade a gift
     */
    public function upgradeGift(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $giftId = $this->required($request, 'gift_id');

            $gift = $this->db->table('user_gifts')
                ->where('id', $giftId)
                ->where('user_id', $userId)
                ->first();

            if (!$gift) {
                return $this->error('Gift not found', 404);
            }

            if (!$gift['can_be_upgraded']) {
                return $this->error('Gift cannot be upgraded', 400);
            }

            $botId = $this->getBotId($token);

            $this->db->table('user_gifts')
                ->where('id', $giftId)
                ->update(['is_upgraded' => true]);

            $this->db->table('star_transactions')->insert([
                'user_id' => $botId,
                'amount' => -50,
                'type' => 'charge',
                'description' => 'Upgrade fee for gift ' . $gift['gift_id'],
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    }
