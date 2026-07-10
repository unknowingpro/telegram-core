<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * Session model — manages user authentication sessions
 */
class SessionModel extends BaseModel
{
    protected string $table = 'sessions';
    protected string $primaryKey = 'id';

    /**
     * Create a new session for a user
     */
    public function createSession(int|string $userId, string $ip, string $userAgent): string
    {
        $token = generate_token(32);

        $this->create([
            'user_id' => $userId,
            'token' => $token,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'expires_at' => date('Y-m-d H:i:s', time() + (int) env('JWT_EXPIRY', 86400)),
        ]);

        return $token;
    }

    /**
     * Find session by token (valid, not expired)
     */
    public function findValid(string $token): ?array
    {
        return $this->db->table($this->table)
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Delete a session (logout)
     */
    public function deleteByToken(string $token): int
    {
        return $this->db->table($this->table)
            ->where('token', $token)
            ->delete();
    }

    /**
     * Delete all sessions for a user
     */
    public function deleteAllForUser(int|string $userId): int
    {
        return $this->db->table($this->table)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Clean up expired sessions
     */
    public function cleanExpired(): int
    {
        return $this->db->table($this->table)
            ->where('expires_at', '<', now())
            ->delete();
    }
}
