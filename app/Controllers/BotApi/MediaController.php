<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\MediaModel;

/**
 * Media controller — getFile method
 */
class MediaController extends BaseController
{
    /**
     * getFile — Get file info and download URL
     */
    public function getFile(Request $request, string $token): Response
    {
        $fileId = $this->required($request, 'file_id');
        $media = (new MediaModel())->findByFileId($fileId);

        if (!$media) {
            return $this->error('File not found', 404);
        }

        return $this->ok((new MediaModel())->toTelegram($media));
    }
}
