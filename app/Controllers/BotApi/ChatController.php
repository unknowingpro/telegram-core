<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\ChatModel;
use App\Models\ChatMemberModel;
use App\Services\MessageService;

/**
 * Chat controller — handles all chat management methods
 * Mirrors Telegram Bot API chat methods exactly
 */
class ChatController extends BaseController
{
    private ChatModel $chatModel;

    public function __construct()
    {
        parent::__construct();
        $this->chatModel = new ChatModel();
    }

    /**
     * getChat — Get chat info
     */
    public function getChat(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $chat = $this->chatModel->find($chatId);

        if (!$chat) {
            return $this->error('Chat not found', 404);
        }

        return $this->ok($this->chatModel->toTelegram($chat));
    }

    /**
     * getChatAdministrators — Get all admins
     */
    public function getChatAdministrators(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $admins = (new ChatMemberModel())->getChatAdmins($chatId);
        return $this->ok($admins);
    }

    /**
     * getChatMemberCount — Get member count
     */
    public function getChatMemberCount(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $count = $this->db->table('chat_members')
            ->where('chat_id', $chatId)
            ->where('status', 'active')
            ->count();
        return $this->ok((int) $count);
    }

    /**
     * getChatMember — Get specific member info
     */
    public function getChatMember(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');

        $member = (new ChatMemberModel())->getMember($chatId, $userId);
        if (!$member) {
            return $this->error('USER_NOT_PARTICIPANT', 400);
        }

        return $this->ok($this->chatModel->toTelegramMember($member));
    }

    /**
     * setChatTitle — Set chat title
     */
    public function setChatTitle(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $title = $this->required($request, 'title');
            $this->chatModel->update($chatId, ['title' => $title]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setChatDescription — Set chat description
     */
    public function setChatDescription(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $description = $this->input($request, 'description', '');
            $this->chatModel->update($chatId, ['description' => $description]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setChatPhoto — Set chat photo
     */
    public function setChatPhoto(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $botId = $this->getBotUserId($token);
            $photo = $this->resolveFileUpload($request, 'photo', $botId) ?? $this->required($request, 'photo');
            $this->chatModel->update($chatId, ['photo_file_id' => $photo]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteChatPhoto — Delete chat photo
     */
    public function deleteChatPhoto(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $this->chatModel->update($chatId, ['photo_file_id' => null]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setChatPermissions — Set default chat permissions
     */
    public function setChatPermissions(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $permissionsRaw = $this->required($request, 'permissions');
            $permissions = is_string($permissionsRaw) ? json_decode($permissionsRaw, true) : $permissionsRaw;

            $existing = $this->db->table('chat_permissions')
                ->where('chat_id', $chatId)
                ->first();

            $data = [
                'chat_id' => $chatId,
                'can_send_messages' => $permissions['can_send_messages'] ?? 1,
                'can_send_media' => $permissions['can_send_media'] ?? 1,
                'can_send_polls' => $permissions['can_send_polls'] ?? 1,
                'can_send_other' => $permissions['can_send_other'] ?? 1,
                'can_add_members' => $permissions['can_add_members'] ?? 0,
                'can_pin_messages' => $permissions['can_pin_messages'] ?? 0,
                'can_change_info' => $permissions['can_change_info'] ?? 0,
            ];

            if ($existing) {
                $this->db->table('chat_permissions')
                    ->where('chat_id', $chatId)
                    ->update($data);
            } else {
                $this->db->table('chat_permissions')->insert($data);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setChatAdministratorCustomTitle — Set admin custom title
     */
    public function setChatAdministratorCustomTitle(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');
        $customTitle = $this->required($request, 'custom_title');

        (new ChatMemberModel())->updateMember($chatId, $userId, ['custom_title' => $customTitle]);
        return $this->ok(true);
    }

    /**
     * setChatMenuButton — Set menu button
     */
    public function setChatMenuButton(Request $request, string $token): Response
    {
        try {
            $chatId = $this->input($request, 'chat_id');
            $menuButtonRaw = $this->required($request, 'menu_button');
            $menuButton = is_string($menuButtonRaw) ? json_decode($menuButtonRaw, true) : $menuButtonRaw;

            $botId = $this->getBotUserId($token);

            // Store menu button preference in bot_accounts as JSON
            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            $menuData = json_encode([
                'chat_id' => $chatId,
                'menu_button' => $menuButton,
            ]);

            if ($existing) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['default_admin_rights' => $menuData]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getChatMenuButton — Get menu button
     */
    public function getChatMenuButton(Request $request, string $token): Response
    {
        $chatId = $this->input($request, 'chat_id');

        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        if ($bot && ($bot['default_admin_rights'] ?? null)) {
            $menuData = json_decode($bot['default_admin_rights'], true);
            if (isset($menuData['menu_button'])) {
                return $this->ok($menuData['menu_button']);
            }
        }

        return $this->ok(['type' => 'default', 'text' => '']);
    }

    /**
     * exportChatInviteLink — Export invite link
     */
    public function exportChatInviteLink(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');

        // Check if there's an existing primary link
        $existing = $this->db->table('invite_links')
            ->where('chat_id', $chatId)
            ->where('is_primary', true)
            ->where('is_revoked', false)
            ->first();

        if ($existing) {
            return $this->ok($existing['invite_link']);
        }

        // Generate a new primary link
        $hash = substr(hash('sha256', $chatId . $token . time()), 0, 16);
        $inviteLink = "https://t.me/+{$hash}";

        $this->db->table('invite_links')->insert([
            'chat_id' => $chatId,
            'creator_id' => $this->getBotUserId($token),
            'invite_link' => $inviteLink,
            'is_primary' => true,
            'is_revoked' => false,
            'creates_join_request' => false,
        ]);

        return $this->ok($inviteLink);
    }

    /**
     * createChatInviteLink — Create invite link
     */
    public function createChatInviteLink(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $hash = substr(hash('sha256', $chatId . $token . random_int(0, PHP_INT_MAX)), 0, 16);
            $inviteLink = "https://t.me/+{$hash}";

            $linkId = $this->db->table('invite_links')->insert([
                'chat_id' => $chatId,
                'creator_id' => $this->getBotUserId($token),
                'invite_link' => $inviteLink,
                'name' => $this->input($request, 'name', ''),
                'expire_date' => $this->intInput($request, 'expire_date') ? date('Y-m-d H:i:s', $this->intInput($request, 'expire_date')) : null,
                'member_limit' => $this->intInput($request, 'member_limit'),
                'creates_join_request' => $this->boolInput($request, 'creates_join_request') ? 1 : 0,
                'is_primary' => false,
                'is_revoked' => false,
            ]);

            $invite = $this->db->table('invite_links')
                ->where('id', $linkId)
                ->first();

            return $this->ok([
                'invite_link' => $inviteLink,
                'creator' => $this->getBotUserId($token),
                'creates_join_request' => (bool) $invite['creates_join_request'],
                'is_primary' => false,
                'is_revoked' => false,
                'name' => $invite['name'] ?: null,
                'expire_date' => $invite['expire_date'] ? strtotime($invite['expire_date']) : null,
                'member_limit' => $invite['member_limit'] ? (int) $invite['member_limit'] : null,
                'pending_join_request_count' => 0,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editChatInviteLink — Edit invite link
     */
    public function editChatInviteLink(Request $request, string $token): Response
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
            if ($this->input($request, 'creates_join_request') !== null) {
                $updates['creates_join_request'] = $this->boolInput($request, 'creates_join_request') ? 1 : 0;
            }

            if (!empty($updates)) {
                $this->db->table('invite_links')
                    ->where('invite_link', $inviteLink)
                    ->update($updates);
            }

            return $this->ok([
                'invite_link' => $inviteLink,
                'creator' => $this->getBotUserId($token),
                'creates_join_request' => (bool) ($updates['creates_join_request'] ?? $existing['creates_join_request']),
                'is_primary' => (bool) $existing['is_primary'],
                'is_revoked' => (bool) $existing['is_revoked'],
                'name' => $updates['name'] ?? $existing['name'],
                'expire_date' => isset($updates['expire_date']) ? strtotime($updates['expire_date']) : ($existing['expire_date'] ? strtotime($existing['expire_date']) : null),
                'member_limit' => isset($updates['member_limit']) ? (int) $updates['member_limit'] : ($existing['member_limit'] ? (int) $existing['member_limit'] : null),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * revokeChatInviteLink — Revoke invite link
     */
    public function revokeChatInviteLink(Request $request, string $token): Response
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

            $this->db->table('invite_links')
                ->where('invite_link', $inviteLink)
                ->update(['is_revoked' => true]);

            return $this->ok([
                'invite_link' => $inviteLink,
                'creator' => $this->getBotUserId($token),
                'is_primary' => (bool) $existing['is_primary'],
                'is_revoked' => true,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * approveChatJoinRequest — Approve join request
     */
    public function approveChatJoinRequest(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');

        (new ChatMemberModel())->addMember($chatId, $userId, 'member');
        return $this->ok(true);
    }

    /**
     * declineChatJoinRequest — Decline join request
     */
    public function declineChatJoinRequest(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');

        (new ChatMemberModel())->removeMember($chatId, $userId);
        return $this->ok(true);
    }

    /**
     * banChatMember — Ban a user
     */
    public function banChatMember(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');
            $untilDate = $this->intInput($request, 'until_date');
            $revokeMessages = $this->boolInput($request, 'revoke_messages', true);

            $model = new ChatMemberModel();
            $model->banMember($chatId, $userId, [
                'until_date' => $untilDate > 0 ? date('Y-m-d H:i:s', $untilDate) : null,
            ]);

            // Optionally revoke all recent messages
            if ($revokeMessages) {
                $messageService = new MessageService();
                // Get message IDs from the last 24 hours for this user in this chat
                $messages = $this->db->table('messages')
                    ->where('chat_id', $chatId)
                    ->where('sender_id', $userId)
                    ->where('created_at', '>', date('Y-m-d H:i:s', time() - 86400))
                    ->pluck('id');

                if (!empty($messages)) {
                    $messageService->deleteMessages($chatId, $messages);
                }
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * unbanChatMember — Unban a user
     * Supports only_if_banned parameter — only unbans if user is currently banned
     */
    public function unbanChatMember(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');
        $onlyIfBanned = $this->boolInput($request, 'only_if_banned');

        $member = (new ChatMemberModel())->getMember($chatId, $userId);

        // If only_if_banned is set, only proceed if user is currently banned
        if ($onlyIfBanned && (!$member || $member['status'] !== 'kicked')) {
            return $this->ok(true);
        }

        (new ChatMemberModel())->removeMember($chatId, $userId);
        return $this->ok(true);
    }

    /**
     * banChatSenderChat — Ban a channel chat in a supergroup or channel
     */
    public function banChatSenderChat(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $senderChatId = $this->required($request, 'sender_chat_id');
            $untilDate = $this->intInput($request, 'until_date');

            (new ChatMemberModel())->banMember($chatId, $senderChatId, [
                'until_date' => $untilDate ? date('Y-m-d H:i:s', $untilDate) : null,
                'is_sender_chat' => true,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * unbanChatSenderChat — Unban a previously banned channel chat
     */
    public function unbanChatSenderChat(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $senderChatId = $this->required($request, 'sender_chat_id');

            (new ChatMemberModel())->removeMember($chatId, $senderChatId);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * restrictChatMember — Restrict a member
     */
    public function restrictChatMember(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');
            $permissionsRaw = $this->required($request, 'permissions');
            $permissions = is_string($permissionsRaw) ? json_decode($permissionsRaw, true) : $permissionsRaw;

            (new ChatMemberModel())->updateMember($chatId, $userId, [
                'restricted_until' => $this->intInput($request, 'until_date') ? date('Y-m-d H:i:s', $this->intInput($request, 'until_date')) : null,
                'restricted_permissions' => json_encode($permissions),
                'use_independent_chat_permissions' => $this->boolInput($request, 'use_independent_chat_permissions'),
            ]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * promoteChatMember — Promote user to admin with granular permissions
     *
     * All 17 permission flags from Telegram Bot API are supported.
     * Stored as JSON in chat_members.admin_permissions.
     * When no flags are passed, defaults to all true (full admin).
     */
    public function promoteChatMember(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');

            // Collect all permission flags (only set explicit Booleans)
            $permissions = [];
            foreach ([
                'is_anonymous', 'can_manage_chat', 'can_delete_messages',
                'can_manage_video_chats', 'can_restrict_members', 'can_promote_members',
                'can_change_info', 'can_invite_users', 'can_post_stories',
                'can_edit_stories', 'can_delete_stories', 'can_post_messages',
                'can_edit_messages', 'can_pin_messages', 'can_manage_topics',
                'can_manage_direct_messages', 'can_manage_tags',
            ] as $flag) {
                $val = $request->input($flag);
                if ($val !== null) {
                    $permissions[$flag] = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                }
            }

            $model = new ChatMemberModel();

            // If no explicit flags, grant full admin (Telegram default behavior)
            if (empty($permissions)) {
                $model->setRole($chatId, $userId, 'admin');
                $model->updateMember($chatId, $userId, ['admin_permissions' => null]);
                return $this->ok(true);
            }

            $model->updateMember($chatId, $userId, [
                'role' => 'admin',
                'admin_permissions' => json_encode($permissions),
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * pinChatMessage — Pin a message
     */
    public function pinChatMessage(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $messageId = $this->required($request, 'message_id');

        $this->db->table('pinned_messages')->insert([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'pinned_by' => $this->getBotUserId($token),
        ]);
        return $this->ok(true);
    }

    /**
     * unpinChatMessage — Unpin a message
     */
    public function unpinChatMessage(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $messageId = $this->input($request, 'message_id');

        $query = $this->db->table('pinned_messages')
            ->where('chat_id', $chatId);

        if ($messageId) {
            $query->where('message_id', $messageId);
        }

        $query->delete();
        return $this->ok(true);
    }

    /**
     * unpinAllChatMessages — Unpin all messages
     */
    public function unpinAllChatMessages(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $this->db->table('pinned_messages')
            ->where('chat_id', $chatId)
            ->delete();
        return $this->ok(true);
    }

    /**
     * leaveChat — Leave a chat
     */
    public function leaveChat(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            (new ChatMemberModel())->removeMember($chatId, $this->getBotUserId($token));
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setChatStickerSet — Set sticker set for chat
     */
    public function setChatStickerSet(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $stickerSetName = $this->required($request, 'sticker_set_name');

            $this->chatModel->update($chatId, ['sticker_set_name' => $stickerSetName]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteChatStickerSet — Delete sticker set from chat
     */
    public function deleteChatStickerSet(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $this->chatModel->update($chatId, ['sticker_set_name' => null]);
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
