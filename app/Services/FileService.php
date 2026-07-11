<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * File service — handles file upload, storage, and serving
 * Mirrors Telegram's file handling (getFile, file links)
 */
class FileService
{
    private Database $db;
    private string $uploadDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->uploadDir = BASE_PATH . '/storage/uploads';
        $this->ensureDirectories();
    }

    /**
     * Store an uploaded file from multipart form data
     */
    public function storeUpload(array $file, int|string $userId, ?string $fileId = null): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed with error code: ' . $file['error']);
        }

        // Generate unique file identifiers
        $fileId = $fileId ?? 'file_' . bin2hex(random_bytes(16));
        $fileUniqueId = 'fu_' . bin2hex(random_bytes(12));

        // Determine storage path
        $ext = $this->getExtension($file['name']);
        $storageName = $fileUniqueId . ($ext ? '.' . $ext : '');
        $relativePath = $this->getStoragePath($storageName);
        $absolutePath = $this->uploadDir . '/' . $relativePath;

        // Ensure subdirectory exists
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            throw new \RuntimeException('Failed to store uploaded file');
        }

        // Detect MIME type
        $mimeType = $file['type'] ?: mime_content_type($absolutePath);
        $fileSize = filesize($absolutePath);

        // Get image dimensions if applicable
        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            [$width, $height] = getimagesize($absolutePath) ?: [null, null];
        }

        // Register in media table
        $this->db->table('media')->insert([
            'user_id' => $userId,
            'file_id' => $fileId,
            'file_unique_id' => $fileUniqueId,
            'file_path' => $relativePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
        ]);

        return [
            'file_id' => $fileId,
            'file_unique_id' => $fileUniqueId,
            'file_size' => $fileSize,
            'file_path' => $relativePath,
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Store a file from a URL (download remote file)
     */
    public function storeFromUrl(string $url, int|string $userId): array
    {
        $fileId = 'file_' . bin2hex(random_bytes(16));
        $fileUniqueId = 'fu_' . bin2hex(random_bytes(12));

        // Download file
        $content = @file_get_contents($url);
        if ($content === false) {
            throw new \RuntimeException('Failed to download file from URL');
        }

        // Determine extension from URL
        $parsed = parse_url($url, PHP_URL_PATH);
        $ext = pathinfo($parsed, PATHINFO_EXTENSION);
        $storageName = $fileUniqueId . ($ext ? '.' . $ext : '');
        $relativePath = $this->getStoragePath($storageName);
        $absolutePath = $this->uploadDir . '/' . $relativePath;

        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($absolutePath, $content);

        $mimeType = mime_content_type($absolutePath);
        $fileSize = filesize($absolutePath);

        // Image dimensions
        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            [$width, $height] = getimagesize($absolutePath) ?: [null, null];
        }

        $this->db->table('media')->insert([
            'user_id' => $userId,
            'file_id' => $fileId,
            'file_unique_id' => $fileUniqueId,
            'file_path' => $relativePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
        ]);

        return [
            'file_id' => $fileId,
            'file_unique_id' => $fileUniqueId,
            'file_size' => $fileSize,
            'file_path' => $relativePath,
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Get the full HTTP URL to download a file by file_id
     */
    public function getFileUrl(string $fileId, string $token): ?string
    {
        $media = $this->db->table('media')
            ->where('file_id', $fileId)
            ->first();

        if (!$media || !$media['file_path']) {
            return null;
        }

        $appUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
        return "{$appUrl}/file/bot{$token}/{$media['file_path']}";
    }

    /**
     * Serve a file from storage
     */
    public function serveFile(string $filePath): void
    {
        $absolutePath = $this->uploadDir . '/' . ltrim($filePath, '/');

        if (!file_exists($absolutePath)) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error_code' => 404, 'description' => 'File not found']);
            exit;
        }

        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $fileSize = filesize($absolutePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Disposition: inline');

        readfile($absolutePath);
        exit;
    }

    /**
     * Validate and save a media file attached to a Bot API request
     * (InputFile - multipart/form-data upload)
     */
    public function processInputFile(string $fieldName, int|string $userId): ?array
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $this->storeUpload($_FILES[$fieldName], $userId);
    }

    private function getStoragePath(string $filename): string
    {
        // Create subdirectories based on hash to avoid flat directory
        $hash = md5($filename);
        return substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $filename;
    }

    private function getExtension(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        // Only allow safe extensions
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg', 'mp3', 'wav',
                     'doc', 'docx', 'pdf', 'zip', 'rar', '7z', 'txt', 'csv', 'xlsx', 'ppt',
                     'pptx', 'mov', 'avi', 'mkv', 'svg', 'ico', 'tga', 'psd', 'ai', 'eps',
                     'flac', 'aac', 'm4a', 'opus', 'tgs', 'json', 'html', 'css', 'js'];
        return in_array($ext, $allowed, true) ? $ext : '';
    }

    private function ensureDirectories(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Create subdirectory levels 00-ff / 00-ff
        for ($i = 0; $i < 256; $i++) {
            $first = str_pad(dechex($i), 2, '0', STR_PAD_LEFT);
            $subDir = $this->uploadDir . '/' . $first;
            if (!is_dir($subDir)) {
                mkdir($subDir, 0755);
            }
        }
    }
}
