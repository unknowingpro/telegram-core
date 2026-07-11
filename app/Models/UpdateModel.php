<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Update model — manages the update queue for long polling
 * Mirrors Telegram's Update type
 */
class UpdateModel extends BaseModel
{
    protected string $table = 'updates';
    protected string $primaryKey = 'id';

    /**
     * Push a new update to the queue
     */
    public function pushUpdate(int|string $userId, string $type, array $payload): string
    {
        return $this->create([
            'user_id' => $userId,
            'update_type' => $type,
            'payload' => json_encode($payload),
            'is_read' => false,
        ]);
    }

    /**
     * Get unread updates for long polling
     * Returns updates starting from the given offset
     */
    public function getUpdates(
        int|string $userId,
        int $offset = 0,
        int $limit = 100,
        ?array $allowedTypes = null
    ): array {
        $query = $this->db->table($this->table)
            ->where('user_id', $userId)
            ->where('is_read', false);

        if ($offset > 0) {
            // Positive offset: return updates with ID greater than offset
            $query = $query->where('id', '>', $offset);
        } elseif ($offset < 0) {
            // Negative offset: retrieve updates starting from -offset from the end
            // All prior updates are forgotten (mark them as read)
            $latestId = $this->getLatestUpdateId($userId);
            $startFrom = max(0, $latestId + $offset + 1);
            $query = $query->where('id', '>=', $startFrom);

            // Mark all updates before the start as read
            if ($startFrom > 0) {
                $this->db->raw(
                    "UPDATE {$this->table} SET is_read = true
                     WHERE user_id = ? AND id < ?",
                    [$userId, $startFrom]
                );
            }
        }

        $updates = $query->orderBy('id', 'ASC')
            ->limit($limit)
            ->get();

        // Filter by allowed types if specified
        if ($allowedTypes) {
            $updates = array_filter($updates, function ($update) use ($allowedTypes) {
                return in_array($update['update_type'], $allowedTypes);
            });
        }

        // Decode payload
        return array_map(function ($update) {
            $update['payload'] = json_decode($update['payload'], true);
            return $update;
        }, array_values($updates));
    }

    /**
     * Mark updates as read (confirm delivery)
     */
    public function markAsRead(int|string $userId, int $offset): int
    {
        return $this->db->raw(
            "UPDATE {$this->table} SET is_read = true
             WHERE user_id = ? AND id <= ?",
            [$userId, $offset]
        )->rowCount();
    }

    /**
     * Get the latest update ID for a user
     */
    public function getLatestUpdateId(int|string $userId): int
    {
        $result = $this->db->rawFetch(
            "SELECT MAX(id) as max_id FROM {$this->table} WHERE user_id = ?",
            [$userId]
        );
        return (int) ($result['max_id'] ?? 0);
    }

    /**
     * Clean old updates (older than 7 days)
     */
    public function cleanOld(int $days = 7): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->db->table($this->table)
            ->where('created_at', '<', $cutoff)
            ->delete();
    }

    /**
     * Convert update to Telegram-compatible array
     */
    public function toTelegram(array $update): array
    {
        $result = [
            'update_id' => (int) $update['id'],
        ];

        // Add the payload as the update type
        $type = $update['update_type'];
        $result[$type] = $update['payload'];

        return $result;
    }
}
