<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\MediaModel;
use App\Services\FileService;

/**
 * Media file controller — serves uploaded files
 * Supports both internal file serving and Telegram-style /file/bot{token}/{file_path}
 */
class MediaFileController extends BaseController
{
    private FileService $fileService;

    public function __construct()
    {
        parent::__construct();
        $this->fileService = new FileService();
    }

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

    /**
     * GET /file/bot{token}/{file_path:.*}
     *
     * Telegram-style file serving — matches the Bot API's getFile download URL format.
     * Verifies the bot token before serving the file.
     */
    public function serveBotFile(Request $request, string $token, string $filePath): Response
    {
        // Verify bot token exists
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->where('is_active', true)
            ->first();

        if (!$bot) {
            return $this->error('Not Found', 404);
        }

        // Check if this bot has permission to access the file
        // For public file serving, any valid bot can serve files
        // For user-specific files, verify user_id matches
        $media = $this->db->table('media')
            ->where('file_path', $filePath)
            ->first();

        if (!$media) {
            return $this->error('Not Found', 404);
        }

        $this->fileService->serveFile($filePath);
        // serveFile() calls exit internally, so we never reach here
        exit;
    }
}
