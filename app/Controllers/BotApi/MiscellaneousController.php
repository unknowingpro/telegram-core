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
    /**
     * answerChatJoinRequestQuery — Answer a chat join request query
     */
    public function answerChatJoinRequestQuery(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');
            $queryId = $this->required($request, 'query_id');
            $answer = $this->boolInput($request, 'answer');
            $cacheTime = $this->intInput($request, 'cache_time', 300);

            $this->db->table('callback_queries')->insert([
                'user_id' => $userId,
                'chat_id' => $chatId,
                'data' => json_encode([
                    'type' => 'chat_join_request',
                    'query_id' => $queryId,
                    'answer' => $answer,
                ]),
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * answerGuestQuery — Answer guest query
     */
    public function answerGuestQuery(Request $request, string $token): Response
    {
        try {
            $queryId = $this->required($request, 'query_id');
            $resultRaw = $this->input($request, 'result');

            return $this->ok([
                'id' => 'guest_' . md5($queryId . time()),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * approveSuggestedPost — Approve suggested post in channel
     */
    public function approveSuggestedPost(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $this->db->table('messages')
                ->where('id', $messageId)
                ->where('chat_id', $chatId)
                ->update(['views' => 1]); // Mark as approved

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * declineSuggestedPost — Decline suggested post in channel
     */
    public function declineSuggestedPost(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageId = $this->required($request, 'message_id');

            $this->db->table('messages')
                ->where('id', $messageId)
                ->where('chat_id', $chatId)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * banChatSenderChat — Ban a channel/sender chat
     */
    public function banChatSenderChat(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $senderChatId = $this->required($request, 'sender_chat_id');

            // Mark as banned in the chat_members for this sender chat
            (new \App\Models\ChatMemberModel())->banMember($chatId, $senderChatId);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * unbanChatSenderChat — Unban a channel/sender chat
     */
    public function unbanChatSenderChat(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $senderChatId = $this->required($request, 'sender_chat_id');

            (new \App\Models\ChatMemberModel())->removeMember($chatId, $senderChatId);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * createChatSubscriptionInviteLink — Create subscription invite link
     */
    public function createChatSubscriptionInviteLink(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $hash = substr(hash('sha256', $chatId . $token . random_int(0, PHP_INT_MAX)), 0, 16);
            $inviteLink = "https://t.me/+{$hash}";

            $subscriptionPeriod = $this->intInput($request, 'subscription_period', 2592000); // 30 days default
            $subscriptionPrice = $this->intInput($request, 'subscription_price', 100);

            $linkId = $this->db->table('invite_links')->insert([
                'chat_id' => $chatId,
                'creator_id' => $this->getBotUserId($token),
                'invite_link' => $inviteLink,
                'name' => $this->input($request, 'name', 'Subscription'),
                'expire_date' => $this->intInput($request, 'expire_date') ? date('Y-m-d H:i:s', $this->intInput($request, 'expire_date')) : null,
                'member_limit' => $this->intInput($request, 'member_limit'),
                'creates_join_request' => true,
                'is_primary' => false,
                'is_revoked' => false,
            ]);

            return $this->ok([
                'invite_link' => $inviteLink,
                'creator' => $this->getBotUserId($token),
                'creates_join_request' => true,
                'is_primary' => false,
                'is_revoked' => false,
                'name' => $this->input($request, 'name', 'Subscription'),
                'subscription_period' => $subscriptionPeriod,
                'subscription_price' => $subscriptionPrice,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editChatSubscriptionInviteLink — Edit subscription invite link
     */
    public function editChatSubscriptionInviteLink(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $inviteLink = $this->required($request, 'invite_link');

            $existing = $this->db->table('invite_links')
                ->where('invite_link', $inviteLink)
                ->first();

            if (!$existing) {
                return $this->error('Invite link not found', 404);
            }

            $updates = [];
            if ($this->input($request, 'name') !== null) {
                $updates['name'] = $this->input($request, 'name');
            }
            if ($this->input($request, 'expire_date') !== null) {
                $updates['expire_date'] = date('Y-m-d H:i:s', $this->intInput($request, 'expire_date'));
            }
            if ($this->input($request, 'member_limit') !== null) {
                $updates['member_limit'] = $this->intInput($request, 'member_limit');
            }

            if (!empty($updates)) {
                $this->db->table('invite_links')
                    ->where('invite_link', $inviteLink)
                    ->update($updates);
            }

            return $this->ok([
                'invite_link' => $inviteLink,
                'creator' => $this->getBotUserId($token),
                'creates_join_request' => true,
                'is_primary' => false,
                'is_revoked' => false,
                'name' => $updates['name'] ?? $existing['name'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteStickerSet — Delete a sticker set
     */
    public function deleteStickerSet(Request $request, string $token): Response
    {
        try {
            $name = $this->required($request, 'name');

            $set = $this->db->table('sticker_sets')
                ->where('name', $name)
                ->first();

            if (!$set) {
                return $this->error('Sticker set not found', 404);
            }

            // Delete all stickers in the set first (CASCADE should handle this)
            $this->db->table('stickers')
                ->where('set_id', $set['id'])
                ->delete();

            $this->db->table('sticker_sets')
                ->where('name', $name)
                ->delete();

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getManagedBotAccessSettings — Get managed bot access settings
     */
    public function getManagedBotAccessSettings(Request $request, string $token): Response
    {
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        return $this->ok([
            'permissions' => $bot ? [
                'can_join_groups' => (bool) $bot['can_join_groups'],
                'can_read_all_group_messages' => (bool) $bot['can_read_all_group_messages'],
                'supports_inline_queries' => (bool) $bot['supports_inline_queries'],
                'can_connect_to_business' => (bool) $bot['can_connect_to_business'],
                'has_main_web_app' => (bool) $bot['has_main_web_app'],
            ] : [],
        ]);
    }

    /**
     * setManagedBotAccessSettings — Set managed bot access settings
     */
    public function setManagedBotAccessSettings(Request $request, string $token): Response
    {
        try {
            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            $updates = [];
            if ($this->input($request, 'can_join_groups') !== null) {
                $updates['can_join_groups'] = $this->boolInput($request, 'can_join_groups') ? 1 : 0;
            }
            if ($this->input($request, 'can_read_all_group_messages') !== null) {
                $updates['can_read_all_group_messages'] = $this->boolInput($request, 'can_read_all_group_messages') ? 1 : 0;
            }
            if ($this->input($request, 'supports_inline_queries') !== null) {
                $updates['supports_inline_queries'] = $this->boolInput($request, 'supports_inline_queries') ? 1 : 0;
            }
            if ($this->input($request, 'can_connect_to_business') !== null) {
                $updates['can_connect_to_business'] = $this->boolInput($request, 'can_connect_to_business') ? 1 : 0;
            }
            if ($this->input($request, 'has_main_web_app') !== null) {
                $updates['has_main_web_app'] = $this->boolInput($request, 'has_main_web_app') ? 1 : 0;
            }

            if ($existing && !empty($updates)) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update($updates);
            } elseif (!empty($updates)) {
                $updates['user_id'] = $this->getBotUserId($token);
                $updates['token'] = $token;
                $this->db->table('bot_accounts')->insert($updates);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getManagedBotToken — Get managed bot token
     */
    public function getManagedBotToken(Request $request, string $token): Response
    {
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        return $this->ok(['token' => $bot['token'] ?? $token]);
    }

    /**
     * replaceManagedBotToken — Replace managed bot token
     */
    public function replaceManagedBotToken(Request $request, string $token): Response
    {
        try {
            $newToken = bin2hex(random_bytes(16));

            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if ($existing) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['token' => $newToken]);
            } else {
                $this->db->table('bot_accounts')->insert([
                    'user_id' => $this->getBotUserId($token),
                    'token' => $newToken,
                ]);
            }

            return $this->ok(['token' => $newToken]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setChatMemberTag — Set tag for chat member
     */
    public function setChatMemberTag(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');
            $tag = $this->required($request, 'tag');

            (new \App\Models\ChatMemberModel())->updateMember($chatId, $userId, [
                'custom_title' => $tag,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getUserPersonalChatMessages — Get personal chat messages
     */
    public function getUserPersonalChatMessages(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $offset = $this->intInput($request, 'offset', 0);
            $limit = min($this->intInput($request, 'limit', 100), 100);

            $messages = $this->db->table('messages')
                ->where('sender_id', $userId)
                ->whereNull('deleted_at')
                ->orderBy('id', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();

            $result = array_map(fn($m) => [
                'message_id' => (int) $m['id'],
                'date' => strtotime($m['created_at']),
                'text' => $m['text'],
                'caption' => $m['caption'],
                'content_type' => $m['content_type'],
            ], $messages);

            return $this->ok(['messages' => $result]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getUserChatBoosts — Get boosts for a chat by a user
     */
    public function getUserChatBoosts(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');

            $boosts = $this->db->table('chat_boosts')
                ->where('chat_id', $chatId)
                ->where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->get();

            $result = array_map(fn($b) => [
                'boost_id' => (string) $b['id'],
                'source' => $b['source'],
                'date' => strtotime($b['created_at']),
                'expire_date' => $b['expire_date'] ? strtotime($b['expire_date']) : null,
                'user' => [
                    'id' => (int) $b['user_id'],
                    'is_bot' => false,
                    'first_name' => 'User',
                ],
            ], $boosts);

            return $this->ok(['boosts' => $result]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * sendChatJoinRequestWebApp — Send a join request via web app
     */
    public function sendChatJoinRequestWebApp(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');

            (new \App\Models\ChatMemberModel())->addMember($chatId, $userId, 'member');
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
