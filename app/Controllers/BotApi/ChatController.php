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
 * Mirrors Telegram Bot API chat methods
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
     * getChatMemberCount — Get member count
     */
    public function getChatMemberCount(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $count = $this->chatModel->count(['id' => $chatId]);
        return $this->ok($count);
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
            return $this->error('Member not found', 404);
        }

        return $this->ok($member);
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
     * setChatTitle — Set chat title
     */
    public function setChatTitle(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $title = $this->required($request, 'title');
        $this->chatModel->update($chatId, ['title' => $title]);
        return $this->ok(true);
    }

    /**
     * setChatDescription — Set chat description
     */
    public function setChatDescription(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $description = $this->input($request, 'description', '');
        $this->chatModel->update($chatId, ['description' => $description]);
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
     * leaveChat — Leave a chat
     */
    public function leaveChat(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $this->chatModel->removeMember($chatId, $this->getBotUserId($token));
        return $this->ok(true);
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
     * restrictChatMember — Restrict a member
     */
    public function restrictChatMember(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $userId = $this->required($request, 'user_id');
        // TODO: Implement permission restrictions
        return $this->ok(true);
    }

    /**
     * pinChatMessage — Pin a message
     */
    public function pinChatMessage(Request $request, string $token): Response
    {
        // TODO: Implement message pinning
        return $this->ok(true);
    }

    public function unpinChatMessage(Request $request, string $token): Response { return $this->ok(true); }
    public function unpinAllChatMessages(Request $request, string $token): Response { return $this->ok(true); }
    public function setChatPhoto(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteChatPhoto(Request $request, string $token): Response { return $this->ok(true); }
    public function setChatPermissions(Request $request, string $token): Response { return $this->ok(true); }
    public function setChatAdministratorCustomTitle(Request $request, string $token): Response { return $this->ok(true); }
    public function setChatMenuButton(Request $request, string $token): Response { return $this->ok(true); }
    public function getChatMenuButton(Request $request, string $token): Response { return $this->ok(true); }
    public function exportChatInviteLink(Request $request, string $token): Response { return $this->ok(true); }
    public function createChatInviteLink(Request $request, string $token): Response { return $this->ok(true); }
    public function editChatInviteLink(Request $request, string $token): Response { return $this->ok(true); }
    public function revokeChatInviteLink(Request $request, string $token): Response { return $this->ok(true); }
    public function approveChatJoinRequest(Request $request, string $token): Response { return $this->ok(true); }
    public function declineChatJoinRequest(Request $request, string $token): Response { return $this->ok(true); }
    public function setChatStickerSet(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteChatStickerSet(Request $request, string $token): Response { return $this->ok(true); }

    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
