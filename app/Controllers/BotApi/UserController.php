<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\UserModel;

/**
 * User controller — profile photos, verification, emoji status
 * Mirrors Telegram Bot API user methods exactly
 */
class UserController extends BaseController
{
    /**
     * getUserProfilePhotos — Get user profile photos
     */
    public function getUserProfilePhotos(Request $request, string $token): Response
    {
        $userId = $this->required($request, 'user_id');
        $offset = $this->intInput($request, 'offset', 0);
        $limit = min($this->intInput($request, 'limit', 100), 100);

        $photos = $this->db->table('media')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $rows = [];
        foreach ($photos as $photo) {
            $rows[] = [[
                'file_id' => $photo['file_id'],
                'file_unique_id' => $photo['file_unique_id'],
                'file_size' => (int) ($photo['file_size'] ?? 0),
                'width' => (int) ($photo['width'] ?? 0),
                'height' => (int) ($photo['height'] ?? 0),
            ]];
        }

        return $this->ok([
            'total_count' => count($photos),
            'photos' => $rows,
        ]);
    }

    /**
     * getUserProfileAudios — Get user profile audio files
     */
    public function getUserProfileAudios(Request $request, string $token): Response
    {
        $userId = $this->required($request, 'user_id');
        $offset = $this->intInput($request, 'offset', 0);
        $limit = min($this->intInput($request, 'limit', 100), 100);

        $audios = $this->db->table('media')
            ->where('user_id', $userId)
            ->where('mime_type', 'LIKE', 'audio/%')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $result = [];
        foreach ($audios as $audio) {
            $result[] = [
                'file_id' => $audio['file_id'],
                'file_unique_id' => $audio['file_unique_id'],
                'duration' => (int) ($audio['duration'] ?? 0),
                'performer' => $audio['performer'] ?? null,
                'title' => $audio['title'] ?? null,
                'file_name' => $audio['file_name'] ?? null,
                'mime_type' => $audio['mime_type'] ?? null,
                'file_size' => (int) ($audio['file_size'] ?? 0),
            ];
        }

        return $this->ok([
            'total_count' => count($audios),
            'audios' => $result,
        ]);
    }

    /**
     * setUserEmojiStatus — Set user emoji status
     */
    public function setUserEmojiStatus(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $emojiStatusCustomEmojiId = $this->required($request, 'emoji_status_custom_emoji_id');
            $expirationDate = $this->intInput($request, 'emoji_status_expiration_date');

            $update = [
                'emoji_status_custom_emoji_id' => $emojiStatusCustomEmojiId,
                'emoji_status_expiration_date' => $expirationDate ? date('Y-m-d H:i:s', $expirationDate) : null,
            ];

            $this->db->table('users')
                ->where('id', $userId)
                ->update($update);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * removeUserVerification — Remove user verification
     */
    public function removeUserVerification(Request $request, string $token): Response
    {
        $userId = $this->required($request, 'user_id');
        $this->db->table('users')->where('id', $userId)->update(['is_verified' => false]);
        return $this->ok(true);
    }

    /**
     * removeChatVerification — Remove chat verification
     */
    public function removeChatVerification(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $this->db->table('chats')->where('id', $chatId)->update(['is_verified' => false]);
        return $this->ok(true);
    }

    /**
     * verifyUser — Verify a user
     */
    public function verifyUser(Request $request, string $token): Response
    {
        $userId = $this->required($request, 'user_id');
        $this->db->table('users')->where('id', $userId)->update(['is_verified' => true]);
        return $this->ok(true);
    }

    /**
     * verifyChat — Verify a chat
     */
    public function verifyChat(Request $request, string $token): Response
    {
        $chatId = $this->required($request, 'chat_id');
        $this->db->table('chats')->where('id', $chatId)->update(['is_verified' => true]);
        return $this->ok(true);
    }

    /**
     * setMyProfilePhoto — Set bot profile photo
     */
    public function setMyProfilePhoto(Request $request, string $token): Response
    {
        try {
            $photo = $this->required($request, 'photo');
            $botId = $this->getBotUserId($token);

            $this->db->table('media')->insert([
                'user_id' => $botId,
                'file_id' => $photo,
                'file_unique_id' => 'unique_' . md5($photo),
                'file_path' => null,
                'file_size' => 0,
                'mime_type' => 'image/jpeg',
                'width' => 640,
                'height' => 640,
            ]);

            $this->db->table('users')
                ->where('id', $botId)
                ->update(['avatar_file_id' => $photo]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * removeMyProfilePhoto — Remove bot profile photo
     */
    public function removeMyProfilePhoto(Request $request, string $token): Response
    {
        $botId = $this->getBotUserId($token);
        $this->db->table('users')
            ->where('id', $botId)
            ->update(['avatar_file_id' => null]);
        return $this->ok(true);
    }

    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
