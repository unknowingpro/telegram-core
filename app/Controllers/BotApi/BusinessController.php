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
    /**
     * setBusinessAccountName — Set business name
     */
    public function setBusinessAccountName(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $name = $this->input($request, 'name', '');
            $firstName = $this->input($request, 'first_name');
            $lastName = $this->input($request, 'last_name');

            $existing = $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->first();

            if ($existing) {
                $updates = [];
                if ($name !== null) $updates['name'] = $name;
                if ($firstName !== null) $updates['name'] = $firstName;
                $this->db->table('business_accounts')
                    ->where('id', $businessConnectionId)
                    ->update($updates);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setBusinessAccountUsername — Set business username
     */
    public function setBusinessAccountUsername(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $username = $this->required($request, 'username');

            $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->update(['username' => $username]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setBusinessAccountBio — Set business bio
     */
    public function setBusinessAccountBio(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $bio = $this->required($request, 'bio');

            $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->update(['bio' => $bio]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setBusinessAccountProfilePhoto — Set business profile photo
     */
    public function setBusinessAccountProfilePhoto(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $photo = $this->required($request, 'photo');

            // Handle file upload if this is an InputFile upload
            $fileId = $this->resolveFileUpload($request, 'photo', 0);
            if ($fileId !== null) {
                $photo = $fileId;
            }

            $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->update(['profile_photo_file_id' => $photo]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * removeBusinessAccountProfilePhoto — Remove business profile photo
     */
    public function removeBusinessAccountProfilePhoto(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');

            $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->update(['profile_photo_file_id' => null]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setBusinessAccountGiftSettings — Set business gift settings
     */
    public function setBusinessAccountGiftSettings(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $showGiftButton = $this->boolInput($request, 'show_gift_button');

            $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->update([
                    'gift_settings' => json_encode([
                        'show_gift_button' => $showGiftButton,
                    ]),
                ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getBusinessConnection — Get business connection info
     */
    public function getBusinessConnection(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');

            $biz = $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->first();

            if (!$biz) {
                return $this->error('Business connection not found', 404);
            }

            return $this->ok([
                'id' => (string) $biz['id'],
                'user' => [
                    'id' => (int) $biz['user_id'],
                    'is_bot' => false,
                    'first_name' => $biz['name'] ?? 'Business',
                ],
                'user_chat_id' => (int) $biz['user_id'],
                'date' => strtotime($biz['created_at']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * readBusinessMessage — Mark business message as read
     */
    public function readBusinessMessage(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $this->db->table('messages')
                ->where('id', $messageId)
                ->where('chat_id', $chatId)
                ->update(['views' => 1]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteBusinessMessages — Delete business messages
     */
    public function deleteBusinessMessages(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $chatId = $this->required($request, 'chat_id');
            $messageIdsRaw = $this->required($request, 'message_ids');
            $messageIds = is_string($messageIdsRaw) ? json_decode($messageIdsRaw, true) : $messageIdsRaw;

            foreach ($messageIds as $messageId) {
                $this->db->table('messages')
                    ->where('id', $messageId)
                    ->where('chat_id', $chatId)
                    ->delete();
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getBusinessAccountStarBalance — Get business star balance
     */
    public function getBusinessAccountStarBalance(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');

            $biz = $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->first();

            if (!$biz) {
                return $this->error('Business connection not found', 404);
            }

            $balance = $this->db->table('star_transactions')
                ->where('user_id', $biz['user_id'])
                ->select('COALESCE(SUM(amount), 0) as balance')
                ->first();

            return $this->ok(['balance' => (int) ($balance['balance'] ?? 0)]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * transferBusinessAccountStars — Transfer stars from business
     */
    public function transferBusinessAccountStars(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');
            $userId = $this->required($request, 'user_id');
            $starCount = (int) $this->required($request, 'star_count');

            $biz = $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->first();

            if (!$biz) {
                return $this->error('Business connection not found', 404);
            }

            // Debit from business
            $this->db->table('star_transactions')->insert([
                'user_id' => $biz['user_id'],
                'amount' => -$starCount,
                'type' => 'charge',
                'description' => 'Star transfer to user ' . $userId,
            ]);

            // Credit to user
            $this->db->table('star_transactions')->insert([
                'user_id' => $userId,
                'amount' => $starCount,
                'type' => 'purchase',
                'description' => 'Star transfer from business ' . $businessConnectionId,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getBusinessAccountGifts — Get gifts for business
     */
    public function getBusinessAccountGifts(Request $request, string $token): Response
    {
        try {
            $businessConnectionId = $this->required($request, 'business_connection_id');

            $biz = $this->db->table('business_accounts')
                ->where('id', $businessConnectionId)
                ->first();

            if (!$biz) {
                return $this->error('Business connection not found', 404);
            }

            $gifts = $this->db->table('user_gifts')
                ->where('user_id', $biz['user_id'])
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
}
