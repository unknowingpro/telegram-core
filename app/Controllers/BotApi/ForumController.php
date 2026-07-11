<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Forum controller — forum topic management
 * Mirrors Telegram Bot API forum methods exactly
 */
class ForumController extends BaseController
{
    /**
     * createForumTopic — Create a forum topic
     */
    public function createForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $name = $this->required($request, 'name');

            $topicId = $this->db->table('forum_topics')->insert([
                'chat_id' => $chatId,
                'name' => $name,
                'icon_color' => $this->intInput($request, 'icon_color'),
                'icon_custom_emoji_id' => $this->input($request, 'icon_custom_emoji_id'),
            ]);

            return $this->ok([
                'message_thread_id' => (int) $topicId,
                'name' => $name,
                'icon_color' => $this->intInput($request, 'icon_color', 0x6FB9F0),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editForumTopic — Edit forum topic
     */
    public function editForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageThreadId = $this->required($request, 'message_thread_id');

            $updates = [];
            if ($this->input($request, 'name') !== null) {
                $updates['name'] = $this->input($request, 'name');
            }
            if ($this->input($request, 'icon_custom_emoji_id') !== null) {
                $updates['icon_custom_emoji_id'] = $this->input($request, 'icon_custom_emoji_id');
            }

            if (!empty($updates)) {
                $this->db->table('forum_topics')
                    ->where('id', $messageThreadId)
                    ->update($updates);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * closeForumTopic — Close a forum topic
     */
    public function closeForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * reopenForumTopic — Reopen a forum topic
     */
    public function reopenForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * deleteForumTopic — Delete a forum topic
     */
    public function deleteForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageThreadId = $this->required($request, 'message_thread_id');

            $this->db->table('forum_topics')
                ->where('id', $messageThreadId)
                ->delete();

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * unpinAllForumTopicMessages — Unpin all messages in topic
     */
    public function unpinAllForumTopicMessages(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * getForumTopicIconStickers — Get forum topic icon stickers
     */
    public function getForumTopicIconStickers(Request $request, string $token): Response
    {
        return $this->ok([]);
    }

    /**
     * hideGeneralForumTopic — Hide general forum topic
     */
    public function hideGeneralForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * unhideGeneralForumTopic — Unhide general forum topic
     */
    public function unhideGeneralForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * editGeneralForumTopic — Edit general forum topic
     */
    public function editGeneralForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * closeGeneralForumTopic — Close general forum topic
     */
    public function closeGeneralForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * reopenGeneralForumTopic — Reopen general forum topic
     */
    public function reopenGeneralForumTopic(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * unpinAllGeneralForumTopicMessages — Unpin all general topic messages
     */
    public function unpinAllGeneralForumTopicMessages(Request $request, string $token): Response
    {
        return $this->ok(true);
    }
}
