<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Stories controller — story posting and management
 * Mirrors Telegram Bot API story methods
 */
class StoriesController extends BaseController
{
    /**
     * postStory — Post a story
     */
    public function postStory(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $mediaFileId = $this->required($request, 'media');
            $chatId = $this->input($request, 'chat_id', $userId);
            $expireInSec = $this->intInput($request, 'expire_in_sec', 86400);

            $storyId = $this->db->table('stories')->insert([
                'user_id' => $userId,
                'chat_id' => $chatId,
                'media_file_id' => $mediaFileId,
                'caption' => $this->input($request, 'caption'),
                'entities' => $this->input($request, 'entities'),
                'parse_mode' => $this->input($request, 'parse_mode'),
                'is_pinned' => $this->boolInput($request, 'is_pinned'),
                'expire_date' => date('Y-m-d H:i:s', time() + $expireInSec),
            ]);

            return $this->ok([
                'id' => (int) $storyId,
                'date' => time(),
                'expire_date' => time() + $expireInSec,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * editStory — Edit a story
     */
    public function editStory(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $storyId = $this->required($request, 'story_id');

            $story = $this->db->table('stories')
                ->where('id', $storyId)
                ->where('user_id', $userId)
                ->first();

            if (!$story) {
                return $this->error('Story not found', 404);
            }

            $updates = [];
            if ($this->input($request, 'caption') !== null) {
                $updates['caption'] = $this->input($request, 'caption');
            }
            if ($this->input($request, 'media') !== null) {
                $updates['media_file_id'] = $this->input($request, 'media');
            }
            if ($this->input($request, 'entities') !== null) {
                $updates['entities'] = $this->input($request, 'entities');
            }
            if ($this->input($request, 'parse_mode') !== null) {
                $updates['parse_mode'] = $this->input($request, 'parse_mode');
            }
            if ($this->input($request, 'is_pinned') !== null) {
                $updates['is_pinned'] = $this->boolInput($request, 'is_pinned');
            }

            if (!empty($updates)) {
                $this->db->table('stories')
                    ->where('id', $storyId)
                    ->update($updates);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteStory — Delete a story
     */
    public function deleteStory(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $storyId = $this->required($request, 'story_id');

            $story = $this->db->table('stories')
                ->where('id', $storyId)
                ->where('user_id', $userId)
                ->first();

            if (!$story) {
                return $this->error('Story not found', 404);
            }

            // Soft delete
            $this->db->table('stories')
                ->where('id', $storyId)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * repostStory — Repost a story from source chat
     */
    public function repostStory(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $storyId = $this->required($request, 'story_id');
            $chatId = $this->required($request, 'chat_id');

            $original = $this->db->table('stories')
                ->where('id', $storyId)
                ->first();

            if (!$original) {
                return $this->error('Story not found', 404);
            }

            $newStoryId = $this->db->table('stories')->insert([
                'user_id' => $userId,
                'chat_id' => $chatId,
                'media_file_id' => $original['media_file_id'],
                'caption' => $original['caption'],
                'entities' => $original['entities'],
                'parse_mode' => $original['parse_mode'],
                'is_pinned' => false,
                'expire_date' => date('Y-m-d H:i:s', time() + 86400),
            ]);

            return $this->ok([
                'id' => (int) $newStoryId,
                'date' => time(),
                'expire_date' => time() + 86400,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getStoryStatistics — Get story view/reaction stats
     */
    public function getStoryStatistics(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $storyId = $this->required($request, 'story_id');

            $story = $this->db->table('stories')
                ->where('id', $storyId)
                ->where('user_id', $userId)
                ->first();

            if (!$story) {
                return $this->error('Story not found', 404);
            }

            // Count reactions on stories via messages linked to this story
            $reactionCount = $this->db->table('message_reactions')
                ->where('message_id', $storyId)
                ->count();

            return $this->ok([
                'views' => (int) ($story['views'] ?? 0),
                'reactions' => (int) $reactionCount,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getUserStories — Get stories for a user
     */
    public function getUserStories(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');

            $stories = $this->db->table('stories')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->where('expire_date', '>', date('Y-m-d H:i:s'))
                ->orderBy('id', 'DESC')
                ->limit(50)
                ->get();

            $result = array_map(fn($s) => [
                'id' => (int) $s['id'],
                'date' => strtotime($s['created_at']),
                'expire_date' => strtotime($s['expire_date']),
                'caption' => $s['caption'],
                'is_pinned' => (bool) $s['is_pinned'],
                'media' => [
                    'file_id' => $s['media_file_id'],
                    'file_unique_id' => 'su_' . md5($s['media_file_id']),
                ],
            ], $stories);

            return $this->ok(['stories' => $result]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
