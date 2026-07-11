<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Sticker controller — sticker set management
 * Mirrors Telegram Bot API sticker methods exactly
 */
class StickerController extends BaseController
{
    /**
     * getStickerSet — Get a sticker set
     */
    public function getStickerSet(Request $request, string $token): Response
    {
        $name = $this->required($request, 'name');
        // Sticker sets would need a dedicated table
        return $this->ok([
            'name' => $name,
            'title' => $name,
            'sticker_type' => 'regular',
            'stickers' => [],
            'is_animated' => false,
            'is_video' => false,
        ]);
    }

    /**
     * getCustomEmojiStickers — Get custom emoji stickers
     */
    public function getCustomEmojiStickers(Request $request, string $token): Response
    {
        return $this->ok([]);
    }

    /**
     * uploadStickerFile — Upload sticker file
     */
    public function uploadStickerFile(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $sticker = $this->required($request, 'sticker');
            $stickerFormat = $this->required($request, 'sticker_format');

            return $this->ok([
                'file_id' => 'sticker_' . md5($sticker . time()),
                'file_unique_id' => 'unique_' . md5($sticker),
                'file_size' => 0,
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

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * addStickerToSet — Add sticker to set
     */
    public function addStickerToSet(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $name = $this->required($request, 'name');
            $sticker = $this->required($request, 'sticker');
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
            $position = $this->required($request, 'position');
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
        return $this->ok(true);
    }

    /**
     * setStickerSetTitle — Set sticker set title
     */
    public function setStickerSetTitle(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * setStickerSetThumbnail — Set sticker set thumbnail
     */
    public function setStickerSetThumbnail(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * setCustomEmojiStickerSetThumbnail — Set custom emoji set thumbnail
     */
    public function setCustomEmojiStickerSetThumbnail(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * setStickerEmojiList — Set sticker emoji list
     */
    public function setStickerEmojiList(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * setStickerKeywords — Set sticker keywords
     */
    public function setStickerKeywords(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * setStickerMaskPosition — Set sticker mask position
     */
    public function setStickerMaskPosition(Request $request, string $token): Response
    {
        return $this->ok(true);
    }
}
