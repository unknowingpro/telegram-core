<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Sticker controller — sticker set management with DB persistence
 * Mirrors Telegram Bot API sticker methods exactly
 */
class StickerController extends BaseController
{
    /**
     * getStickerSet — Get a sticker set with all its stickers
     */
    public function getStickerSet(Request $request, string $token): Response
    {
        try {
            $name = $this->required($request, 'name');

            $set = $this->db->table('sticker_sets')
                ->where('name', $name)
                ->first();

            if (!$set) {
                return $this->error('Sticker set not found', 404);
            }

            $stickers = $this->db->table('stickers')
                ->where('set_id', $set['id'])
                ->orderBy('position', 'ASC')
                ->get();

            $stickerData = array_map(fn($s) => [
                'file_id' => $s['file_id'],
                'file_unique_id' => $s['file_unique_id'],
                'type' => $s['type'],
                'width' => (int) $s['width'],
                'height' => (int) $s['height'],
                'is_animated' => (bool) $s['is_animated'],
                'is_video' => (bool) $s['is_video'],
                'emoji' => $s['emoji'] ?? null,
                'file_size' => (int) ($s['file_size'] ?? 0),
            ], $stickers);

            return $this->ok([
                'name' => $set['name'],
                'title' => $set['title'],
                'sticker_type' => $set['sticker_type'],
                'stickers' => $stickerData,
                'is_animated' => (bool) $set['is_animated'],
                'is_video' => (bool) $set['is_video'],
                'thumbnail' => $set['thumbnail_file_id'] ? ['file_id' => $set['thumbnail_file_id'], 'file_unique_id' => 'thumb_' . $set['thumbnail_file_id'], 'width' => 100, 'height' => 100, 'file_size' => 0] : null,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getCustomEmojiStickers — Get custom emoji stickers
     */
    public function getCustomEmojiStickers(Request $request, string $token): Response
    {
        $customEmojiIdsRaw = $this->required($request, 'custom_emoji_ids');
        $customEmojiIds = is_string($customEmojiIdsRaw) ? json_decode($customEmojiIdsRaw, true) : $customEmojiIdsRaw;

        $stickers = [];
        foreach ($customEmojiIds as $emojiId) {
            $sticker = $this->db->table('stickers')
                ->where('file_unique_id', $emojiId)
                ->first();
            if ($sticker) {
                $stickers[] = [
                    'file_id' => $sticker['file_id'],
                    'file_unique_id' => $sticker['file_unique_id'],
                    'type' => $sticker['type'],
                    'width' => (int) $sticker['width'],
                    'height' => (int) $sticker['height'],
                    'is_animated' => (bool) $sticker['is_animated'],
                    'is_video' => (bool) $sticker['is_video'],
                    'emoji' => $sticker['emoji'] ?? null,
                    'file_size' => (int) ($sticker['file_size'] ?? 0),
                ];
            }
        }

        return $this->ok($stickers);
    }

    /**
     * uploadStickerFile — Upload sticker file
     */
    public function uploadStickerFile(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $sticker = $this->input($request, 'sticker'); // InputFile (multipart upload or file_id)
            $stickerFormat = $this->required($request, 'sticker_format');

            // Try to save the uploaded file physically
            $fileResult = $this->resolveFileUpload($request, 'sticker', $userId, 'sticker_');

            if ($fileResult === null) {
                // No file uploaded — generate a placeholder record
                $fileId = 'sticker_' . bin2hex(random_bytes(8));
                $fileUniqueId = 's_' . bin2hex(random_bytes(6));

                $this->db->table('media')->insert([
                    'user_id' => $userId,
                    'file_id' => $fileId,
                    'file_unique_id' => $fileUniqueId,
                    'file_size' => 0,
                    'mime_type' => $stickerFormat === 'static' ? 'image/webp' : ($stickerFormat === 'animated' ? 'application/x-tgsticker' : 'video/webm'),
                ]);

                return $this->ok([
                    'file_id' => $fileId,
                    'file_unique_id' => $fileUniqueId,
                    'file_size' => 0,
                ]);
            }

            // File was uploaded — return saved file info
            $media = $this->db->table('media')
                ->where('file_id', $fileResult)
                ->first();

            return $this->ok([
                'file_id' => $media['file_id'],
                'file_unique_id' => $media['file_unique_id'],
                'file_size' => (int) ($media['file_size'] ?? 0),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * createNewStickerSet — Create new sticker set
     */
    public function createNewStickerSet(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $name = $this->required($request, 'name');
            $title = $this->required($request, 'title');
            $stickersRaw = $this->required($request, 'stickers');
            $stickerType = $this->input($request, 'sticker_type', 'regular');
            $needsRepainting = $this->boolInput($request, 'needs_repainting');
            $stickers = is_string($stickersRaw) ? json_decode($stickersRaw, true) : $stickersRaw;

            $setId = $this->db->table('sticker_sets')->insert([
                'name' => $name,
                'title' => $title,
                'sticker_type' => $stickerType,
                'is_animated' => $stickerType === 'animated' || str_ends_with($name, '_animated'),
                'is_video' => $stickerType === 'video',
                'owner_id' => $userId,
            ]);

            foreach ($stickers as $i => $s) {
                $this->db->table('stickers')->insert([
                    'set_id' => $setId,
                    'file_id' => $s['sticker'] ?? $s['file_id'] ?? '',
                    'file_unique_id' => 'su_' . md5($s['sticker'] ?? $s['file_id'] ?? ''),
                    'type' => $s['type'] ?? $stickerType,
                    'emoji' => $s['emoji'] ?? null,
                    'position' => $i,
                    'file_size' => 0,
                    'width' => $s['width'] ?? 512,
                    'height' => $s['height'] ?? 512,
                    'is_animated' => $stickerType === 'animated',
                    'is_video' => $stickerType === 'video',
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * addStickerToSet — Add sticker to existing set
     */
    public function addStickerToSet(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $name = $this->required($request, 'name');
            $stickerRaw = $this->required($request, 'sticker');
            $sticker = is_string($stickerRaw) ? json_decode($stickerRaw, true) : $stickerRaw;

            $set = $this->db->table('sticker_sets')->where('name', $name)->first();
            if (!$set) {
                return $this->error('Sticker set not found', 404);
            }

            $maxPos = $this->db->table('stickers')
                ->where('set_id', $set['id'])
                ->count();

            $this->db->table('stickers')->insert([
                'set_id' => $set['id'],
                'file_id' => $sticker['sticker'] ?? '',
                'file_unique_id' => 'su_' . md5($sticker['sticker'] ?? ''),
                'emoji' => $sticker['emoji'] ?? null,
                'position' => $maxPos,
                'file_size' => 0,
                'width' => $sticker['width'] ?? 512,
                'height' => $sticker['height'] ?? 512,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setStickerPositionInSet — Set sticker position
     */
    public function setStickerPositionInSet(Request $request, string $token): Response
    {
        try {
            $sticker = $this->required($request, 'sticker');
            $position = (int) $this->required($request, 'position');

            $this->db->table('stickers')
                ->where('file_id', $sticker)
                ->update(['position' => $position]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteStickerFromSet — Delete sticker from set
     */
    public function deleteStickerFromSet(Request $request, string $token): Response
    {
        try {
            $sticker = $this->required($request, 'sticker');

            $this->db->table('stickers')
                ->where('file_id', $sticker)
                ->delete();

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * replaceStickerInSet — Replace sticker in set
     */
    public function replaceStickerInSet(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $name = $this->required($request, 'name');
            $oldSticker = $this->required($request, 'old_sticker');
            $stickerRaw = $this->required($request, 'sticker');
            $sticker = is_string($stickerRaw) ? json_decode($stickerRaw, true) : $stickerRaw;

            $this->db->table('stickers')
                ->where('file_id', $oldSticker)
                ->update([
                    'file_id' => $sticker['sticker'] ?? '',
                    'emoji' => $sticker['emoji'] ?? null,
                ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setStickerSetTitle — Set sticker set title
     */
    public function setStickerSetTitle(Request $request, string $token): Response
    {
        try {
            $name = $this->required($request, 'name');
            $title = $this->required($request, 'title');

            $this->db->table('sticker_sets')
                ->where('name', $name)
                ->update(['title' => $title]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setStickerSetThumbnail — Set sticker set thumbnail
     */
    public function setStickerSetThumbnail(Request $request, string $token): Response
    {
        try {
            $name = $this->required($request, 'name');
            $userId = $this->required($request, 'user_id');
            $thumbnail = $this->input($request, 'thumbnail');

            $this->db->table('sticker_sets')
                ->where('name', $name)
                ->update(['thumbnail_file_id' => $thumbnail]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setCustomEmojiStickerSetThumbnail — Set custom emoji set thumbnail
     */
    public function setCustomEmojiStickerSetThumbnail(Request $request, string $token): Response
    {
        try {
            $name = $this->required($request, 'name');
            $customEmojiId = $this->required($request, 'custom_emoji_id');

            $this->db->table('sticker_sets')
                ->where('name', $name)
                ->update(['thumbnail_file_id' => $customEmojiId]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setStickerEmojiList — Set sticker emoji list
     */
    public function setStickerEmojiList(Request $request, string $token): Response
    {
        try {
            $sticker = $this->required($request, 'sticker');
            $emojiListRaw = $this->required($request, 'emoji_list');
            $emojiList = is_string($emojiListRaw) ? json_decode($emojiListRaw, true) : $emojiListRaw;

            $this->db->table('stickers')
                ->where('file_id', $sticker)
                ->update(['emoji' => implode('', $emojiList)]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setStickerKeywords — Set sticker keywords
     */
    public function setStickerKeywords(Request $request, string $token): Response
    {
        try {
            $sticker = $this->required($request, 'sticker');
            $keywordsRaw = $this->required($request, 'keywords');
            $keywords = is_string($keywordsRaw) ? json_decode($keywordsRaw, true) : $keywordsRaw;

            $this->db->table('stickers')
                ->where('file_id', $sticker)
                ->update(['keywords' => is_array($keywords) ? json_encode($keywords) : $keywords]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setStickerMaskPosition — Set sticker mask position
     */
    public function setStickerMaskPosition(Request $request, string $token): Response
    {
        try {
            $sticker = $this->required($request, 'sticker');
            $maskPositionRaw = $this->required($request, 'mask_position');
            $maskPosition = is_string($maskPositionRaw) ? json_decode($maskPositionRaw, true) : $maskPositionRaw;

            $this->db->table('stickers')
                ->where('file_id', $sticker)
                ->update(['mask_position' => json_encode($maskPosition)]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
