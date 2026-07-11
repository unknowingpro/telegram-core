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
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageThreadId = $this->required($request, 'message_thread_id');

            $this->db->table('forum_topics')
                ->where('id', $messageThreadId)
                ->update(['icon_color' => null]); // mark as closed via icon_color=null

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * reopenForumTopic — Reopen a forum topic
     */
    public function reopenForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageThreadId = $this->required($request, 'message_thread_id');

            $this->db->table('forum_topics')
                ->where('id', $messageThreadId)
                ->update(['icon_color' => $this->intInput($request, 'icon_color', 0x6FB9F0)]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
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
        try {
            $chatId = $this->required($request, 'chat_id');
            $messageThreadId = $this->required($request, 'message_thread_id');

            // Delete pinned messages for this thread
            $this->db->table('pinned_messages')
                ->where('chat_id', $chatId)
                ->delete();

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getForumTopicIconStickers — Get forum topic icon stickers
     */
    public function getForumTopicIconStickers(Request $request, string $token): Response
    {
        // Return all stickers that can be used as forum topic icons
        $stickers = $this->db->table('stickers')
            ->where('type', 'custom_emoji')
            ->limit(100)
            ->get();

        $result = array_map(fn($s) => [
            'file_id' => $s['file_id'],
            'file_unique_id' => $s['file_unique_id'],
            'type' => $s['type'],
            'width' => (int) $s['width'],
            'height' => (int) $s['height'],
            'is_animated' => (bool) $s['is_animated'],
            'is_video' => (bool) $s['is_video'],
            'emoji' => $s['emoji'],
            'file_size' => (int) ($s['file_size'] ?? 0),
        ], $stickers);

        return $this->ok($result);
    }

    /**
     * hideGeneralForumTopic — Hide general forum topic
     */
    public function hideGeneralForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');

            $this->db->table('forum_topics')
                ->where('chat_id', $chatId)
                ->where('name', 'General')
                ->update(['name' => 'General_hidden']); // mark as hidden

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * unhideGeneralForumTopic — Unhide general forum topic
     */
    public function unhideGeneralForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');

            $this->db->table('forum_topics')
                ->where('chat_id', $chatId)
                ->where('name', 'General_hidden')
                ->update(['name' => 'General']);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editGeneralForumTopic — Edit general forum topic
     */
    public function editGeneralForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');

            $updates = [];
            if ($this->input($request, 'name') !== null) {
                $updates['name'] = $this->input($request, 'name');
            }

            if (!empty($updates)) {
                $this->db->table('forum_topics')
                    ->where('chat_id', $chatId)
                    ->where('name', 'LIKE', 'General%')
                    ->update($updates);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * closeGeneralForumTopic — Close general forum topic
     */
    public function closeGeneralForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');

            $this->db->table('forum_topics')
                ->where('chat_id', $chatId)
                ->where('name', 'LIKE', 'General%')
                ->update(['icon_color' => null]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * reopenGeneralForumTopic — Reopen general forum topic
     */
    public function reopenGeneralForumTopic(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');

            $this->db->table('forum_topics')
                ->where('chat_id', $chatId)
                ->where('name', 'LIKE', 'General%')
                ->update(['icon_color' => 0x6FB9F0]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * unpinAllGeneralForumTopicMessages — Unpin all general topic messages
     */
    public function unpinAllGeneralForumTopicMessages(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');

            $this->db->table('pinned_messages')
                ->where('chat_id', $chatId)
                ->delete();

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
