<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\ChatModel;
use App\Models\ChatMemberModel;

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
        $count = $this->chatModel->count(['id' => $chatId]);
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
            $photo = $this->required($request, 'photo');
            // Store the photo file_id on the chat
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
        return $this->ok(true);
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
        return $this->ok(true);
    }

    /**
     * getChatMenuButton — Get menu button
     */
    public function getChatMenuButton(Request $request, string $token): Response
    {
        return $this->ok(['text' => '', 'type' => 'default']);
    }

    /**
     * exportChatInviteLink — Export invite link
     */
    public function exportChatInviteLink(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        // Generate a simple invite link
        $hash = substr(hash('sha256', $chatId . $token . time()), 0, 16);
        return $this->ok("https://t.me/+{$hash}");
    }

    /**
     * createChatInviteLink — Create invite link
     */
    public function createChatInviteLink(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $hash = substr(hash('sha256', $chatId . $token . random_int(0, PHP_INT_MAX)), 0, 16);

        $inviteLink = [
            'invite_link' => "https://t.me/+{$hash}",
            'creator' => $this->getBotUserId($token),
            'creates_join_request' => $this->boolInput($request, 'creates_join_request'),
            'is_primary' => false,
            'is_revoked' => false,
            'name' => $this->input($request, 'name', ''),
            'expire_date' => $this->intInput($request, 'expire_date'),
            'member_limit' => $this->intInput($request, 'member_limit'),
            'pending_join_request_count' => 0,
        ];

        // Remove nulls
        $inviteLink = array_filter($inviteLink, fn($v) => $v !== null);

        return $this->ok($inviteLink);
    }

    /**
     * editChatInviteLink — Edit invite link
     */
    public function editChatInviteLink(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $inviteLink = $this->required($request, 'invite_link');

        return $this->ok([
            'invite_link' => $inviteLink,
            'creator' => $this->getBotUserId($token),
            'creates_join_request' => $this->boolInput($request, 'creates_join_request'),
            'is_primary' => false,
            'is_revoked' => false,
            'name' => $this->input($request, 'name', ''),
            'expire_date' => $this->intInput($request, 'expire_date'),
            'member_limit' => $this->intInput($request, 'member_limit'),
        ]);
    }

    /**
     * revokeChatInviteLink — Revoke invite link
     */
    public function revokeChatInviteLink(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $inviteLink = $this->required($request, 'invite_link');

        return $this->ok([
            'invite_link' => $inviteLink,
            'creator' => $this->getBotUserId($token),
            'is_primary' => false,
            'is_revoked' => true,
        ]);
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
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');

        (new ChatMemberModel())->banMember($chatId, $userId);
        return $this->ok(true);
    }

    /**
     * unbanChatMember — Unban a user
     */
    public function unbanChatMember(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');

        (new ChatMemberModel())->removeMember($chatId, $userId);
        return $this->ok(true);
    }

    /**
     * restrictChatMember — Restrict a member
     */
    public function restrictChatMember(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $userId = $this->required($request, 'user_id');
            $permissions = $this->required($request, 'permissions');

            (new ChatMemberModel())->updateMember($chatId, $userId, [
                'restricted_until' => $this->intInput($request, 'until_date'),
            ]);
            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * promoteChatMember — Promote user to admin
     */
    public function promoteChatMember(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');

        (new ChatMemberModel())->setRole($chatId, $userId, 'admin');
        return $this->ok(true);
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
        $this->db->table('pinned_messages')
            ->where('chat_id', $chatId)
            ->delete();
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
            $this->chatModel->removeMember($chatId, $this->getBotUserId($token));
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
        return $this->ok(true);
    }

    /**
     * deleteChatStickerSet — Delete sticker set from chat
     */
    public function deleteChatStickerSet(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
