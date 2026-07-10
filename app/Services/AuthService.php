<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;
use App\Models\SessionModel;

/**
 * Auth service — handles registration, login, JWT management
 */
class AuthService
{
    private UserModel $users;
    private SessionModel $sessions;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->sessions = new SessionModel();
    }

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Validate required fields
        if (empty($data['phone']) || empty($data['password']) || empty($data['first_name'])) {
            throw new \InvalidArgumentException('phone, password, and first_name are required');
        }

        // Check if phone already exists
        if ($this->users->findByPhone($data['phone'])) {
            throw new \InvalidArgumentException('Phone number already registered');
        }

        // Check username uniqueness if provided
        if (!empty($data['username'])) {
            if ($this->users->findByUsername($data['username'])) {
                throw new \InvalidArgumentException('Username already taken');
            }
        }

        // Create user
        $userId = $this->users->register($data);

        // Create session
        $user = $this->users->find($userId);
        $token = $this->sessions->createSession(
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        return [
            'user' => $this->users->toTelegram($user),
            'token' => $token,
        ];
    }

    /**
     * Login with phone and password
     */
    public function login(string $phone, string $password, string $ip = '', string $userAgent = ''): array
    {
        $user = $this->users->verifyCredentials($phone, $password);
        if (!$user) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        // Update last seen
        $this->users->touchLastSeen($user['id']);

        // Create session
        $token = $this->sessions->createSession($user['id'], $ip, $userAgent);

        return [
            'user' => $this->users->toTelegram($user),
            'token' => $token,
        ];
    }

    /**
     * Logout (invalidate session)
     */
    public function logout(string $token): bool
    {
        return $this->sessions->deleteByToken($token) > 0;
    }

    /**
     * Validate a token and return the user
     */
    public function validateToken(string $token): ?array
    {
        $session = $this->sessions->findValid($token);
        if (!$session) {
            return null;
        }

        $user = $this->users->find($session['user_id']);
        if (!$user) {
            return null;
        }

        // Update last seen
        $this->users->touchLastSeen($user['id']);

        return $user;
    }

    /**
     * Get the current authenticated user from request
     */
    public function getCurrentUser(?string $token): ?array
    {
        if (!$token) {
            return null;
        }
        return $this->validateToken($token);
    }
}
