<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

/**
 * User model — handles user CRUD and lookups
 * Mirrors Telegram's User type
 */
class UserModel extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Create a new user (registration)
     */
    public function register(array $data): string
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        $data['id'] = $this->generateId();
        $data['created_at'] = now();
        $data['last_seen_at'] = now();

        return $this->create($data);
    }

    /**
     * Find user by phone number
     */
    public function findByPhone(string $phone): ?array
    {
        return $this->findBy('phone', $phone);
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    /**
     * Verify login credentials
     */
    public function verifyCredentials(string $phone, string $password): ?array
    {
        $user = $this->findByPhone($phone);
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    /**
     * Update last seen timestamp
     */
    public function touchLastSeen(int|string $userId): void
    {
        $this->update($userId, ['last_seen_at' => now()]);
    }

    /**
     * Convert user to Telegram-compatible array
     */
    public function toTelegram(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'is_bot' => (bool) ($user['is_bot'] ?? false),
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'] ?? null,
            'username' => $user['username'] ?? null,
            'language_code' => $user['language_code'] ?? null,
            'is_premium' => (bool) ($user['is_premium'] ?? false),
        ];
    }
}
