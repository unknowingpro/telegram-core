<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Sticker controller — sticker set management
 */
class StickerController extends BaseController
{
    public function getStickerSet(Request $request, string $token): Response { return $this->ok(null); }
    public function getCustomEmojiStickers(Request $request, string $token): Response { return $this->ok([]); }
    public function uploadStickerFile(Request $request, string $token): Response { return $this->ok(true); }
    public function createNewStickerSet(Request $request, string $token): Response { return $this->ok(true); }
    public function addStickerToSet(Request $request, string $token): Response { return $this->ok(true); }
    public function setStickerPositionInSet(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteStickerFromSet(Request $request, string $token): Response { return $this->ok(true); }
    public function replaceStickerInSet(Request $request, string $token): Response { return $this->ok(true); }
    public function setStickerSetTitle(Request $request, string $token): Response { return $this->ok(true); }
    public function setStickerSetThumbnail(Request $request, string $token): Response { return $this->ok(true); }
    public function setCustomEmojiStickerSetThumbnail(Request $request, string $token): Response { return $this->ok(true); }
    public function setStickerEmojiList(Request $request, string $token): Response { return $this->ok(true); }
    public function setStickerKeywords(Request $request, string $token): Response { return $this->ok(true); }
    public function setStickerMaskPosition(Request $request, string $token): Response { return $this->ok(true); }
}
