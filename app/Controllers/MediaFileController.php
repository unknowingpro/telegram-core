<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\MediaModel;

/**
 * Media file controller — serves uploaded files
 */
class MediaFileController extends BaseController
{
    /**
     * GET /file/{file_id}
     */
    public function serve(Request $request, string $fileId): Response
    {
        $media = (new MediaModel())->findByFileId($fileId);

        if (!$media || empty($media['file_path'])) {
            return $this->error('File not found', 404);
        }

        $filePath = BASE_PATH . '/storage/uploads/' . $media['file_path'];

        if (!file_exists($filePath)) {
            return $this->error('File not found on disk', 404);
        }

        // Serve file with correct content type
        header('Content-Type: ' . ($media['mime_type'] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        exit;
    }
}
