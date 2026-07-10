<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Static file controller — serves assets
 */
class StaticController extends BaseController
{
    /**
     * GET /assets/{path}
     */
    public function serve(Request $request, string $path): Response
    {
        $filePath = BASE_PATH . '/public/assets/' . $path;

        // Prevent directory traversal
        $realPath = realpath($filePath);
        $assetsDir = realpath(BASE_PATH . '/public/assets');

        if (!$realPath || !str_starts_with($realPath, $assetsDir)) {
            return $this->error('Not found', 404);
        }

        if (!file_exists($filePath)) {
            return $this->error('Not found', 404);
        }

        // Determine content type
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];

        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
}
