<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Media model — manages uploaded files (photos, videos, documents, etc.)
 * Mirrors Telegram's File type
 */
class MediaModel extends BaseModel
{
    protected string $table = 'media';
    protected string $primaryKey = 'id';

    /**
     * Register a new uploaded file
     */
    public function register(array $data): string
    {
        $data['file_id'] = $data['file_id'] ?? generate_token(16);
        $data['file_unique_id'] = $data['file_unique_id'] ?? generate_token(8);
        $data['created_at'] = now();

        return $this->create($data);
    }

    /**
     * Find media by file_id
     */
    public function findByFileId(string $fileId): ?array
    {
        return $this->findBy('file_id', $fileId);
    }

    /**
     * Find media by file_unique_id
     */
    public function findByUniqueFileId(string $uniqueFileId): ?array
    {
        return $this->findBy('file_unique_id', $uniqueFileId);
    }

    /**
     * Get media uploaded by a user
     */
    public function getUserMedia(int|string $userId, ?string $type = null, int $limit = 100): array
    {
        $query = $this->db->table($this->table)
            ->where('user_id', $userId);

        if ($type) {
            $query = $query->where('mime_type', 'LIKE', "{$type}%");
        }

        return $query->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Convert media to Telegram-compatible File object
     */
    public function toTelegram(array $media): array
    {
        return [
            'file_id' => $media['file_id'],
            'file_unique_id' => $media['file_unique_id'],
            'file_size' => (int) ($media['file_size'] ?? 0),
            'file_path' => $media['file_path'] ?? null,
        ];
    }

    /**
     * Convert to PhotoSize array (for photos)
     */
    public function toPhotoSize(array $media): array
    {
        return [
            'file_id' => $media['file_id'],
            'file_unique_id' => $media['file_unique_id'],
            'width' => (int) ($media['width'] ?? 0),
            'height' => (int) ($media['height'] ?? 0),
            'file_size' => (int) ($media['file_size'] ?? 0),
        ];
    }
}
